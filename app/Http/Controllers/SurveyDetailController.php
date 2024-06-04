<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use App\Models\SurveyDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SurveyDetailController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'survey_name' => 'required|max:255',
            'survey_description' => 'required',
            'survey_type' => 'required',
            'survey_start_date' => 'required',
            'survey_end_date' => 'required',
        ]);

        $survey = new SurveyDetail;
        $survey->survey_id = Str::uuid();
        $survey->survey_name = $request->survey_name;
        $survey->survey_description = $request->survey_description;
        $survey->survey_slug = Str::slug($request->survey_name);
        $survey->survey_type = $request->survey_type;
        $survey->survey_start_date = $request->survey_start_date;
        $survey->survey_end_date = $request->survey_end_date;
        $survey->survey_owner = Auth::user()->id;
        $survey->save();

        return response([
            'message' => 'Survey created successfully',
            'survey' => $survey
        ], 201);
    }
}