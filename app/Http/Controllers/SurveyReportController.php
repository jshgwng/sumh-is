<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use League\Csv\Writer;
use App\Models\Question;
use App\Models\Response;
use Illuminate\Http\Request;
use App\Exports\ResponsesExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
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

            // Radio questions
            $radio_questions = $questions->where('type', 'radio');

            // select questions 
            $select_questions = $questions->where('type', 'select');

            // get all responses for each question
            foreach ($checkbox_questions as $question) {
                $checkbox_responses = [];
                foreach ($responses as $key => $response) {
                    // $val = json_decode($response->response);
                    // foreach ($val as $value) {
                        if ($response->question_id == $question->question_id) {
                            $checkbox_responses[$question->question][] = $response->response;
                        }
                    // }
                }


                // count the number of responses for each option
                foreach ($checkbox_responses as $key => $value) {
                    $checkbox_responses[$key] = array_count_values($value);
                }


                // format the data for recharts
                foreach ($checkbox_responses as $key => $value) {
                    $temp = [];
                    foreach ($value as $k => $v) {
                        $temp[] = [
                            'name' => $k,
                            'value' => $v
                        ];
                    }
                    $bar_chart_data[$key] = $temp;
                }
            }
            // foreach ($checkbox_questions as $question) {
            //     $checkbox_responses = [];
            //     foreach ($responses as $key => $response) {
            //         $val = json_decode($response->response);
            //         foreach ($val as $value) {
            //             if ($value->question_id == $question->question_id) {
            //                 $checkbox_responses[$question->question][] = $value->response;
            //             }
            //         }
            //     }


            //     // count the number of responses for each option
            //     foreach ($checkbox_responses as $key => $value) {
            //         $checkbox_responses[$key] = array_count_values($value);
            //     }


            //     // format the data for recharts
            //     foreach ($checkbox_responses as $key => $value) {
            //         $temp = [];
            //         foreach ($value as $k => $v) {
            //             $temp[] = [
            //                 'name' => $k,
            //                 'value' => $v
            //             ];
            //         }
            //         $bar_chart_data[$key] = $temp;
            //     }
            // }
            foreach ($radio_questions as $question) {
                $radio_responses = [];
                foreach ($responses as $key => $response) {
                    // $val = json_decode($response->response);
                    // foreach ($val as $value) {
                        if ($response->question_id == $question->question_id) {
                            $radio_responses[$question->question][] = $response->response;
                        }
                    // }
                }


                // count the number of responses for each option
                foreach ($radio_responses as $key => $value) {
                    $radio_responses[$key] = array_count_values($value);
                }


                // format the data for recharts
                foreach ($radio_responses as $key => $value) {
                    $temp = [];
                    foreach ($value as $k => $v) {
                        $temp[] = [
                            'name' => $k,
                            'value' => $v
                        ];
                    }
                    $bar_chart_data[$key] = $temp;
                }
            }

            // get all responses for each question
            // foreach ($questions as $question) {
            //     foreach ($responses as $response) {
            //         // $val = json_decode($response->response);
            //         // foreach ($val as $value) {
            //             if ($response->question_id == $question->question_id) {

            //                 if (isset($response->grid)) {
            //                     $survey_responses[$question->question][] = str_replace("-", " ", $response->grid) . ' - ' . $response->response;
            //                 } else {
            //                     $survey_responses[$question->question][] = $response->response;
            //                 }
            //             }
            //         // }
            //     }
            // }

            // $sorting_questions = [];
            // foreach ($questions as $key => $question) {
            //     $sorting_questions[$key]['question'] = $question->question;
            //     foreach ($responses as $key => $response) {
            //         $val = json_decode($response->response);
            //         foreach ($val as $value) {
            //             if ($value->question_id == $question->question_id) {

            //                 $sorting_questions[$key]['responses'][] = $value->response;
            //             }
            //         }
            //     }
            // }

            // $responseData = DB::table('responses')->select('responses.response_id', 'json_data.question_id', 'json_data.response')->crossJoin(DB::raw("JSON_TABLE(responses.response, '$[*]' COLUMNS (question_id VARCHAR(255) PATH '$.question_id', response VARCHAR(255) PATH '$.response')) AS json_data"))->where('responses.survey_id', $survey->survey_id)->get();

            $responseData = DB::table('responses')->select(['responses.user_id', 'responses.question_id', 'responses.response', 'responses.created_at'])->where('responses.survey_id', $survey->survey_id)->get();

            // $responseData = DB::table('responses')->select('responses.response_id', DB::raw("JSON_EXTRACT(response, '$.question_id') AS question_id"), DB::raw("JSON_EXTRACT(response, '$.response') AS response"))->where('responses.survey_id', $survey->survey_id)->get();
        

            // count of radio responses for each question & replace the question_id with the question
            $radio_responses = [];
            $part_A = [];
            $part_B = [];

            foreach ($responseData as $key => $value) {
                $question = Question::where('question_id', $value->question_id)->first();
                if($question != null){
                if ($question->type == 'radio') {
                    // $radio_responses[$question->question][] = $value->response;
                    if (isset($question->section) && $question->section == 'PART A') {
                        $part_A[$question->question][] = $value->response;
                    } else if (isset($question->section) && $question->section == 'PART B') {
                        $part_B[$question->question][] = $value->response;
                    } else {
                        $radio_responses[$question->question][] = $value->response;
                    }
                }
                }
            }

            // part A
            foreach ($part_A as $key => $value) {
                $part_A[$key] = array_count_values($value);
                foreach ($part_A[$key] as $k => $v) {
                    $part_A[$key][$k] = [
                        'count' => $v,
                        'percentage' => round(($v / count(
                            $responseData->groupBy('user_id')
                        )) * 100, 2)
                    ];
                }
            }

            // part B
            foreach ($part_B as $key => $value) {
                $part_B[$key] = array_count_values($value);
                foreach ($part_B[$key] as $k => $v) {
                    $part_B[$key][$k] = [
                        'count' => $v,
                        'percentage' => round(($v / count(
                            $responseData->groupBy('user_id')
                        )) * 100, 2)
                    ];
                }
            }


            // count the number of responses for each option & assign percentages
            // foreach ($radio_responses as $key => $value) {
            //     $radio_responses[$key] = array_count_values($value);
            //     foreach ($radio_responses[$key] as $k => $v) {
            //         $radio_responses[$key][$k] = [
            //             'count' => $v,
            //             'percentage' => round(($v / count(
            //                 $responseData->groupBy('user_id')
            //             )) * 100, 2)
            //         ];
            //     }
            // }



            // get all responses for each question and distinct timestamps 
            foreach ($responseData as $key => $value) {
                $result = Question::where('question_id', $value->question_id)->first();

                if ($result !== null) {
                    $responseData[$key]->question_id = Question::where('question_id', $value->question_id)->first()->question;
                }
                continue;
            }

            // group by response_id
            $responseData = $responseData->groupBy('user_id');

            // transform response into array if question id is the same
            foreach ($responseData as $key => $value) {
                $temp = [];
                foreach ($value as $k => $v) {
                    if (isset($temp[$v->question_id])) {
                        $temp[$v->question_id] = $temp[$v->question_id] . ', ' . $v->response;
                        continue;
                    }
                    $temp[$v->question_id] = $v->response;
                }
                $responseData[$key] = $temp;
            }

            $timestamps = [];
            $user_responses = $responses->groupBy('user_id');

            // get distinct timestamps
            foreach ($user_responses as $key => $value) {
                foreach ($value as $k => $v) {
                    $timestamps[$v->user_id] = $v->created_at;
                }
            }

            $timestamps = array_unique($timestamps);

            // add timestamps to each user responseData
            // $newData = [];
            // foreach ($responseData as $key => $value) {
            //     foreach ($timestamps as $k => $v) {
            //         if ($key == $k) {
            //             $newData[$key]['timestamp'] = $v;
            //             $newData[$key] = $value;
            //         }
            //     }
            // }
            

            return response()->json([
                'survey' => $survey,
                'responses' => $survey_responses,
                'bar_chart_data' => $bar_chart_data,
                'answers' => $responses,
                // 'sorting_questions' => $sorting_questions,
                'questions' => $questions,
                'responseData' => $responseData,
                'radio_responses' => $radio_responses,
                // 'nivo_chart_data' => $nivo_chart_data,
                'part_A' => $part_A,
                'part_B' => $part_B,
                'timestamps' => $timestamps,
            ], 200);
        } catch (\Throwable $th) {
            report($th);
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
        foreach ($questions as $question) {
            if ($question->type == 'heading' || $question->type == 'paragraph') {
                continue;
            }
            $column_headers[] = $question->question;
        }

        // add column headers to array
        // $survey_responses[] = $column_headers;

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

    public function csvExport(Request $request)
    {
        $survey = Survey::where('slug', $request->slug)->first();

        // $responseData = DB::table('responses')->select('responses.response_id', 'json_data.question_id', 'json_data.response')->crossJoin(DB::raw("JSON_TABLE(responses.response, '$[*]' COLUMNS (question_id VARCHAR(255) PATH '$.question_id', response VARCHAR(255) PATH '$.response')) AS json_data"))->where('responses.survey_id', $survey->survey_id)->get();

        $responseData = $responseData = DB::table('responses')->select(['responses.user_id', 'responses.question_id', 'responses.response'])->where('responses.survey_id', $survey->survey_id)->get();

        // replace question_id with question
        // foreach ($responseData as $key => $value) {
        //     $responseData[$key]->question_id = Question::where('question_id', $value->question_id)->first()->question;
        // }
        foreach ($responseData as $key => $value) {
            $result = Question::where('question_id', $value->question_id)->first();

            if ($result !== null) {
                $responseData[$key]->question_id = Question::where('question_id', $value->question_id)->first()->question;
            }
            continue;
        }

        // group by response_id
        $responseData = $responseData->groupBy('user_id');

        // transform response into array if question id is the same
        foreach ($responseData as $key => $value) {
            $temp = [];
            foreach ($value as $k => $v) {
                if (isset($temp[$v->question_id])) {
                    $temp[$v->question_id] = $temp[$v->question_id] . ', ' . $v->response;
                    continue;
                }
                $temp[$v->question_id] = $v->response;
            }
            $responseData[$key] = $temp;
        }

        // transform into csv format
        // question keys as column headers
        $csv_data = [];
        // Get all question keys;
        $question_keys = [];
        foreach ($responseData as $key => $value) {
            foreach ($value as $k => $v) {
                if (!in_array($k, $question_keys)) {
                    $question_keys[] = $k;
                }
            }
        }
        $csv_data[] = $question_keys;

        foreach ($responseData as $key => $value) {
            $temp = [];
            foreach ($question_keys as $k => $v) {
                if (isset($value[$v])) {
                    $temp[] = $value[$v];
                    continue;
                }
                $temp[] = '';
            }
            $csv_data[] = $temp;
        }

        // transform into csv format
        $csv = Writer::createFromFileObject(new \SplTempFileObject());
        $csv->setDelimiter(',');
        $csv->setEnclosure('"');

        foreach ($csv_data as $key => $value) {
            $csv->insertOne($value);
        }
        // $csv->insertAll($csv_data);
        // sum if question_id is the same & response is a number

        
        // foreach ($responseData as $key => $value) {
        //     if (is_numeric($value->response)) {
        //         if (isset($responses[$value->question_id])) {
        //             $responses[$value->question_id] += $value->response;
        //             continue;
        //         }
        //         $responses[$value->question_id] = $value->response;
        //     }
        // }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="survey.csv"',
        ]);
        // $csv->output('survey.csv');
    }

    public function getResponse(Request $request, $slug, $id)
    {
        try {
            $user = $request->user();

            abort_if(Gate::denies('get survey', $user), 403, 'You are not authorized to view this page');

            $survey = Survey::where('slug', $slug)->first();

            $questions = Question::where('survey_id', $survey->survey_id)->get();

            $response = Response::where('response_id', $id)->first();

            $val = json_decode($response->response);

            // kessler question 

            $survey_responses = [];

            foreach ($questions as $key => $question) {
                foreach ($val as $key => $value) {
                    if ($value->question_id == $question->question_id) {
                        // if key exists, append to array
                        if (isset($survey_responses[$question->question])) {
                            $survey_responses[$question->question] .= ', ' . $value->response;
                        } else {
                            $survey_responses[$question->question] = $value->response;
                        }
                        // $survey_responses[$question->question] = $value->response;
                    }
                }
            }

            return response()->json([
                'responses' => $survey_responses,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function kesslerCalculator(Request $request)
    {
        try {
            $user = $request->user();

            abort_if(Gate::denies('get survey', $user), 403, 'You are not authorized to view this page');

            $survey = Survey::where('slug', $request->slug)->first();

            $question = Question::where('question_id', $request->question_id)->first();
            $options = json_decode($question->options);

            $responses = Response::where('response_id', $request->response_id)->first();
            $val = json_decode($responses->response);

            $is_k10 = false;

            if (isset($options->options)) {
                foreach ($options->options as $key => $option) {
                    // check if option contains string In the past 4 weeks
                    if (strpos($option, 'In the past 4 weeks') !== false) {
                        $is_k10 = true;
                    }
                }
            }

            $total = 0;
            $ranking = 0;
            $color_coding = '';

            if ($is_k10) {
                foreach ($val as $key => $value) {
                    if ($value->question_id == $request->question_id) {
                        $total += $value->response;
                    }
                }

                switch ($total) {
                    case $total >= 10 && $total <= 15:
                        $ranking = 'Low risk';
                        $color_coding = 'green';
                        break;
                    case $total >= 16 && $total <= 21:
                        $ranking = 'Moderate risk';
                        $color_coding = 'yellow';
                        break;
                    case $total >= 22 && $total <= 29:
                        $ranking = 'High risk';
                        $color_coding = 'orange';
                        break;
                    case $total >= 30 && $total <= 50:
                        $ranking = 'Very High risk';
                        $color_coding = 'red';
                        break;

                    default:
                        $ranking = 'No risk';
                        break;
                }
            }

            return response()->json([
                'is_k10' => $is_k10,
                'total' => $total,
                'ranking' => $ranking,
                'color_coding' => $color_coding,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function initialResponses(Request $request)
    {
        try {
            $user = $request->user();

            abort_if(Gate::denies('get survey', $user), 403, 'You are not authorized to view this page');

            $survey = Survey::where('slug', $request->slug)->first();

            $survey_responses = [];

            $questions = Question::where('survey_id', $survey->survey_id)->get();

            $responses = Response::where('survey_id', $survey->survey_id)->first();

            $val = json_decode($responses->response);

            foreach ($questions as $key => $question) {
                foreach ($val as $key => $value) {
                    if ($value->question_id == $question->question_id) {
                        if (isset($survey_responses[$question->question])) {
                            $survey_responses[$question->question] .= ', ' . $value->response;
                        } else {
                            $survey_responses[$question->question] = $value->response;
                        }
                    }
                }
            }

            return response()->json([
                'responses' => $survey_responses,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    // function to generate random hsl color
    public function randomColor()
    {
        $hue = rand(0, 360);
        $saturation = rand(0, 100);
        $lightness = rand(0, 100);

        return "hsl($hue, $saturation%, $lightness%)";
    }

    public function exportToExcel(Request $request, $id)
    {
        $survey = Survey::where('survey_id', $id)->first();

        $questions = Question::where('survey_id', $survey->survey_id)->get();

        $responses = Response::where('survey_id', $survey->survey_id)->get();

        $surveys_responses = [];

        foreach ($responses as $key => $response) {
            $val = json_decode($response->response);
            foreach ($val as $key => $value) {
                $surveys_responses[$response->response_id][$value->question_id] = $value->response;
            }
        }

        // array of questions
        // $questions_array = [];
        // $responses_array = [];
        // foreach ($questions as $key => $question) {
        //     if ($question->type == 'heading' || $question->type == 'paragraph') {
        //         continue;
        //     }
        //     $questions_array[] = $question->question;

        //     foreach ($responses as $key => $response) {
        //         $val = json_decode($response->response);
        //         foreach ($val as $key => $value) {
        //             if ($value->question_id == $question->question_id) {
        //                 $responses_array[$response->response_id][] = $value->response;
        //             }
        //         }
        //     }
        // }

        // array of responses


        return response()->json([
            'responses' => $surveys_responses,
            'message' => 'Export to excel',
        ], 200);
    }

    public function summarizeKesslerResponses(Request $request){
        $survey = Survey::where('slug', $request->slug)->first();

        $questions = Question::where('survey_id', $survey->survey_id)->get();
        $responses = Response::where('survey_id', $survey->survey_id)->get();

        $k10_question = null;

        foreach ($questions as $key => $question) {
            if ($question->type == 'heading' || $question->type == 'paragraph') {
                continue;
            }

            $options = json_decode($question->options);

            if (isset($options->options)) {
                foreach ($options->options as $key => $option) {
                    // check if option contains string In the past 4 weeks
                    if (strpos($option, 'In the past 4 weeks') !== false) {
                        $k10_question = $question;
                    }
                }
            }
        }

        $k10_responses = [];
        if ($k10_question != null) {
            foreach ($responses as $key => $response) {
                $val = json_decode($response->response);
                // foreach ($val as $key => $value) {
                    if ($response->question_id == $k10_question->question_id) {
                        if (isset($k10_responses[$response->user_id])) {
                            // sum up the responses
                            $k10_responses[$response->user_id] += $response->response;
                            // $k10_responses[$response->user_id] .= ', ' . $response->response;
                        } else {
                            $k10_responses[$response->user_id] = $response->response;
                        }
                    }
                // }
            }
        }

        $summary = [];

        foreach ($k10_responses as $key => $k10_response) {
            if ($k10_response >= 10 && $k10_response <= 15) {
                if (isset($summary['Low'])) {
                    $summary['Low']['count'] += 1;
                } else {
                    $summary['Low']['count'] = 1;
                }
            } else if($k10_response >= 16 && $k10_response <= 21){
                if (isset($summary['Moderate'])) {
                    $summary['Moderate']['count'] += 1;
                } else {
                    $summary['Moderate']['count'] = 1;
                }
            } elseif ($k10_response >= 22 && $k10_response <= 29) {
                if (isset($summary['High'])) {
                    $summary['High']['count'] += 1;
                } else {
                    $summary['High']['count'] = 1;
                }
            } elseif ($k10_response >= 30 && $k10_response <= 50) {
                if (isset($summary['Very High'])) {
                    $summary['Very High']['count'] += 1;
                } else {
                    $summary['Very High']['count'] = 1;
                }
            } elseif ($k10_response >= 30) {
                if (isset($summary['Extremely High'])) {
                    $summary['Extremely High']['count'] += 1;
                } else {
                    $summary['Extremely High']['count'] = 1;
                }
            }
        }

        // calculate percentage
        $total = count($k10_responses);
        foreach ($summary as $key => $value) {
            $summary[$key]['percentage'] = round(($value['count'] / $total) * 100, 2);
        }


        return response()->json([
            'message' => 'Summarize Kessler responses',
            'data' => $summary,
        ], 200);
    }
}