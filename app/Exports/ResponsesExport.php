<?php

namespace App\Exports;

use App\Models\Question;
use App\Models\Response;
use Maatwebsite\Excel\Concerns\FromCollection;

class ResponsesExport implements FromCollection
{

    // survey_id
    public $survey_id;
    public function __construct($survey_id)
    {
        $this->survey_id = $survey_id;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // return Response::all();
        $questions = Question::where('survey_id', $this->survey_id)->get();
        $responses = Response::where('survey_id', $this->survey_id)->get();

        $survey_responses = [];

        foreach ($questions as $question) {
            foreach ($responses as $response) {
                $val = json_decode($response->response);
                foreach ($val as $value) {
                    if ($value->question_id == $question->question_id) {
                        $survey_responses[$question->question][] = $value->response;
                    }
                }
            }
        }

        return collect($survey_responses);
    }
}
