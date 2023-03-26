<?php

namespace App\Http\Controllers;

use App\Models\Response;
use App\Models\Survey;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // get all surveys with count of responses
        $surveys = Survey::withCount('responses')->get();

        // total users who responded to surveys
        $total_users = Response::select('user_id')->distinct()->count();

        $summaries = [
            'Surveys' => $surveys->count(),
            'Responses' => $surveys->sum('responses_count'),
            'Active' => $surveys->where('is_active', 1)->count(),
        ];


        return response()->json([
            'surveys' => $surveys,
            'summary' => $summaries,
        ], 200);
    }
}