<?php

namespace App\Http\Controllers;

use App\Models\Response;
use App\Models\Survey;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class ResponseController extends Controller
{
    public function saveResponse(Request $request)
    {
        $responses = $request->responses;

        // get user uuid if is not anonymous else generate uuid for user if anonymous
        $survey = Survey::where('survey_id', $request->survey_id)->first();

        $user_id = null;
        if ($survey->is_anonymous == 0) {
            $user_id = Str::uuid();
        } else {
            $user_id = auth()->user()->uuid;
        }

        $response = Response::create([
            'response_id' => Str::uuid(),
            'survey_id' => $survey->survey_id,
            'user_id' => $user_id,
            'response' => json_encode($responses),
            'is_anonymous' => $survey->is_anonymous,
        ]);

        return response()->json([
            'message' => 'Responses saved successfully',
            'token' => $user_id,
        ], 200);
    }
}