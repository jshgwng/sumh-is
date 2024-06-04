<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use App\Models\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        try {

            $user = $request->user();
            abort_if(Gate::denies('view dashboard', $user), 403, 'You are not authorized to view this page');
            // $surveys = Survey::withCount('responses')->get();
            $surveys = Survey::withCount(['responses', 'responses as responses_count' => function ($query) {
                $query->select(\DB::raw("COUNT(DISTINCT user_id)"));
            }])->get();

            // count responses by distinct user_id
            $responses = Response::select('user_id')->distinct()->get();

            $summaries = [
                'Surveys' => $surveys->count(),
                'Responses' => $responses->count(),
                'Active' => $surveys->where('is_active', 1)->count(),
            ];


            return response()->json([
                'surveys' => $surveys,
                'summary' => $summaries,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}