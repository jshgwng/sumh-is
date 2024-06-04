<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('survey_details', function (Blueprint $table) {
            $table->id();
            $table->uuid('survey_id')->unique();
            $table->string('survey_name');
            $table->text('survey_description')->nullable();
            $table->string('survey_slug')->unique();
            $table->string('survey_status')->default('draft');
            $table->string('survey_type')->default('public');
            $table->string('survey_category')->nullable();
            $table->date('survey_start_date')->nullable();
            $table->date('survey_end_date')->nullable();
            $table->foreignUuid('survey_owner')->references('id')->on('users');
            $table->string('survey_image')->nullable();
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
        Schema::dropIfExists('survey_details');
    }
};