<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use Illuminate\Http\Request;

class SurveyReportController extends Controller
{
    public function getSurvey(Request $request, $slug)
    {
        $survey = Survey::where('slug', $slug)->first();

        $responses = $survey->responses()->get();

        $questions = $survey->questions()->get();

        $survey_responses = [];

        // get all responses for each question
        foreach($questions as $question){
            foreach ($responses as $response) {
                $val = json_decode($response->response);
                foreach ($val as $value) {
                    if ($value->question_id == $question->question_id) {
                        $survey_responses[$question->question][] = $value->response;
                    }
                }
            }
        }

        return response()->json([
            'survey' => $survey,
            'responses' => $survey_responses,
        ], 200);
    }
}