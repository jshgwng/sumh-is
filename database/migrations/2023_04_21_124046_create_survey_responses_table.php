<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('survey_responses', function (Blueprint $table) {
            $table->id();
            $table->uuid('survey_response_id')->unique();
            $table->uuid('survey_id')->references('survey_id')->on('survey_details');
            $table->uuid('question_id')->references('survey_question_id')->on('survey_questions');
            $table->text('response')->nullable();
            $table->string('respondent');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('survey_responses');
    }
};
