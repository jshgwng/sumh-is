<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use App\Models\Response;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ResponseController extends Controller
{
    public function saveResponse(Request $request)
    {
        try {
            $user = $request->user();
            // abort_if(Gate::denies('save response', $user), 403, 'You are not authorized to view this page');
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
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function responseReports(Request $request)
    {
        try {
            $user = $request->user();
            abort_if(Gate::denies('view response reports', $user), 403, 'You are not authorized to view this page');
            $survey = Survey::where('survey_id', $request->survey_id)->first();

            $questions = $survey->questions()->get();


            return response()->json([
                'message' => 'Responses fetched successfully',
                'questions' => [$questions, $survey],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}