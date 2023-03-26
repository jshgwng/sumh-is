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
        Schema::create('surveys', function (Blueprint $table) {
            $table->id();
            $table->uuid('survey_id')->unique();
            $table->string('title');
            $table->string('slug');
            $table->text('description');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->boolean('is_active');
            $table->boolean('is_public');
            $table->boolean('is_anonymous');
            $table->string('image_url')->default('https://cdn.pixabay.com/photo/2020/07/30/12/06/question-mark-5450222__340.jpg');
            $table->foreignUuid('user_id')->constrained('users');
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
        Schema::dropIfExists('surveys');
    }
};
