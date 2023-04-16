<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Survey;
use Illuminate\Http\Request;
use App\Exports\ResponsesExport;
use Illuminate\Support\Facades\Gate;
use League\Csv\Writer;
use Maatwebsite\Excel\Facades\Excel;

class SurveyReportController extends Controller
{
    public function getSurvey(Request $request, $slug)
    {
        try {
            $user = $request->user();

            abort_if(Gate::denies('get survey', $user), 403, 'You are not authorized to view this page');
            $survey = Survey::where('slug', $slug)->first();

            $responses = $survey->responses()->get();

            $questions = $survey->questions()->get();

            $survey_responses = [];

            $bar_chart_data = [];

            // get all checkbox questions
            $checkbox_questions = $questions->where('type', 'checkbox');

            // get all responses for each question
            foreach ($checkbox_questions as $question) {
                $checkbox_responses = [];
                foreach ($responses as $key => $response) {
                    $val = json_decode($response->response);
                    foreach ($val as $value) {
                        if ($value->question_id == $question->question_id) {
                            $checkbox_responses[$question->question][] = $value->response;
                        }
                    }
                }
                // count the number of responses for each option
                foreach ($checkbox_responses as $key => $value) {
                    $checkbox_responses[$key] = array_count_values($value);
                }

                // format the data for recharts
                foreach ($checkbox_responses as $key => $value) {
                    $temp = [];
                    foreach ($value as $k => $v) {
                        $temp[] = ['name' => $k, 'amt' => $v];
                    }
                    $bar_chart_data[$key] = $temp;
                }
            }

            // get all responses for each question
            foreach ($questions as $question) {
                foreach ($responses as $response) {
                    $val = json_decode($response->response);
                    foreach ($val as $value) {
                        if ($value->question_id == $question->question_id) {

                            if (isset($value->grid)) {
                                $survey_responses[$question->question][] = str_replace("-", " ", $value->grid) . ' - ' . $value->response;
                            } else {
                                $survey_responses[$question->question][] = $value->response;
                            }
                        }
                    }
                }
            }





            return response()->json([
                'survey' => $survey,
                'responses' => $survey_responses,
                'bar_chart_data' => $bar_chart_data,
                'answers' => $responses,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function handleReports(Request $request, $id)
    {
        try {
            return Excel::download(new ResponsesExport($id), 'survey.xlsx');
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function exportToCsv(Request $request, $id)
    {
        $survey = Survey::where('survey_id', $id)->first();

        $questions = $survey->questions()->get();
        $responses = $survey->responses()->get();

        $survey_responses = [];

        $column_headers = [];
        // create array of questions
        foreach($questions as $question) {
            $column_headers[] = $question->question;
        }

        // add column headers to array
        $survey_responses[] = $column_headers;

        // Create array of responses
        foreach ($responses as $response) {
            $val = json_decode($response->response);
            $temp = [];
            foreach ($val as $value) {
                $temp[] = $value->response;
            }
            $survey_responses[] = $temp;
        }


        $csv = Writer::createFromString('');
        $csv->insertAll($survey_responses);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="survey.csv"',
        ]);
    }
}