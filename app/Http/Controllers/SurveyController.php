<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use App\Models\Question;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SurveyController extends Controller
{
    public function saveSurvey(Request $request)
    {
        try {
            $user = $request->user();

            abort_if(Gate::denies('save survey', $user), 403, 'You are not authorized to view this page');

            $request->validate([
                'title' => 'required|string',
                'description' => 'required|string',
                'startDate' => 'required|date',
            ]);

            $survey = null;
            if ($request->id) {
                // update the survey
                $survey = $user->surveys()->where('survey_id', $request->id)->first();

                if ($survey->title != $request->title) {
                    $slug = Str::slug($request->title);
                    $count = Survey::where('slug', 'like', $slug . '%')->count();
                    if ($count > 0) {
                        $slug = $slug . '-' . $count;
                    }
                    $survey->slug = $slug;
                    $survey->save();
                }

                $survey->update([
                    'title' => $request->title,
                    'description' => $request->description,
                    'start_date' => $request->startDate,
                    'end_date' => $request->endDate,
                    'is_active' => $request->isActive,
                    'is_public' => $request->isPublic,
                    'is_anonymous' => $request->isAnonymous,
                ]);
            } else {
                $slug = Str::slug($request->title);
                $count = Survey::where('slug', 'like', $slug . '%')->count();
                if ($count > 0) {
                    $slug = $slug . '-' . $count;
                }
                $survey = $user->surveys()->create([
                    'survey_id' => Str::uuid(),
                    'title' => $request->title,
                    'description' => $request->description,
                    'slug' => $slug,
                    'start_date' => $request->startDate,
                    'end_date' => $request->endDate,
                    'is_active' => $request->isActive,
                    'is_public' => $request->isPublic,
                    'is_anonymous' => $request->isAnonymous,
                ]);
            }

            // fetch the updated survey
            $survey = Survey::where('survey_id', $survey->survey_id)->first();

            return response()->json(['status' => true, 'message' => 'survey saved', 'data' => $survey]);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => 'Something went wrong', 'error' => $th->getMessage()]);
        }
    }

    public function saveQuestionsToSurvey(Request $request)
    {
        try {
            $user = $request->user();

            abort_if(Gate::denies('save survey', $user), 403, 'You are not authorized to view this page');

            $survey = $user->surveys()->where('survey_id', $request->survey_id)->first();

            $request->validate([
                'questions' => 'required|array',
                'questions.*.question' => 'required|string',
                'questions.*.description' => 'nullable|string',
                'questions.*.type' => 'required|string',
                'questions.*.options' => 'nullable|array',
                'questions.*.is_required' => 'required|boolean',
            ]);

            $questions = $request->questions;

            // insert if question does not exist, update if exist, delete if question in db but not in request
            $saved_questions = Question::where('survey_id', $survey->survey_id)->get();

            foreach ($questions as $value) {

                if (array_key_exists('question_id', $value)) {
                    // update
                    $q = $survey->questions()->where('question_id', $value['question_id'])->first();
                    $q->update($value);
                } else {
                    $survey->questions()->create([
                        'question_id' => Str::uuid(),
                        'question' => $value['question'],
                        'description' => $value['description'],
                        'type' => $value['type'],
                        'options' => json_encode($value['options']),
                        'is_required' => $value['is_required'],
                    ]);
                }

            }
            $saved_questions->whereNotIn('question_id', collect($questions)->pluck('question_id'))->each->delete();

            // fetch the survey with questions
            $survey = Survey::with('questions')->where('survey_id', $request->survey_id)->first();

            // transform the options to array
            foreach ($survey->questions as $question) {
                $question->options = json_decode($question->options);
            }

            return response()->json(['status' => true, 'message' => 'survey saved', 'data' => $survey]);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => 'Something went wrong', 'error' => $th->getMessage()]);
        }
    }

    public function getSurveys(Request $request)
    {
        try {
            $user = $request->user();

            // abort_if(Gate::denies('get surveys', $user), 403, 'You are not authorized to view this page');

            $surveys = Survey::with('questions')
                ->where('is_active', true)
                ->get();

            return response()->json(['status' => true, 'message' => 'surveys', 'data' => $surveys]);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => 'Something went wrong', 'error' => $th->getMessage()]);
        }
    }

    public function getSurvey(Request $request, $slug)
    {
        try {
            $user = $request->user();

            // abort_if(Gate::denies('get survey', $user), 403, 'You are not authorized to view this page');
            $survey = Survey::with('questions')->where('slug', $slug)
                ->where('is_active', true)
                ->first();

            // transform the options to array
            foreach ($survey->questions as $question) {
                $question->options = json_decode($question->options);
            }

            return response()->json(['status' => true, 'message' => 'survey', 'data' => $survey]);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => 'Something went wrong', 'error' => $th->getMessage()]);
        }
    }

    public function show(Request $request, $slug)
    {
        try {
            $user = $request->user();

            abort_if(Gate::denies('get survey', $user), 403, 'You are not authorized to view this page');
            $survey = Survey::with('questions')->where('slug', $slug)
                ->first();

            // transform the options to array
            foreach ($survey->questions as $question) {
                $question->options = json_decode($question->options);
            }

            return response()->json(['status' => true, 'message' => 'survey', 'data' => $survey]);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => 'Something went wrong', 'error' => $th->getMessage()]);
        }
    }

    public function deleteSurvey(Request $request, $id)
    {
        try {
            $user = $request->user();

            abort_if(Gate::denies('delete survey', $user), 403, 'You are not authorized to view this page');
            $survey = $user->surveys()->where('survey_id', $id)->first();

            $survey->delete();

            return response()->json(['status' => true, 'message' => 'survey deleted']);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function changeSurveyStatus(Request $request, $id)
    {
        try {
            $user = $request->user();

            abort_if(Gate::denies('save survey', $user), 403, 'You are not authorized to view this page');
            $survey = $user->surveys()->where('survey_id', $id)->first();

            $survey->is_active = !$survey->is_active;
            $survey->save();

            return response()->json(['status' => true, 'message' => 'survey status changed']);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}