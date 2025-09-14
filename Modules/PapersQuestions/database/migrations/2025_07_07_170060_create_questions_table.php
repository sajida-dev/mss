<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('paper_id');
            $table->text('text');
            $table->string('type');
            $table->json('options')->nullable();
            $table->string('answer')->nullable();
            $table->integer('marks')->default(1);
            $table->integer('question_number')->nullable();
            $table->string('section')->nullable(); // objective, short_questions, long_questions, essay
            $table->string('clo')->nullable(); // Course Learning Outcome
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('paper_id')->references('id')->on('papers')->onDelete('cascade');
            $table->index('paper_id');
            $table->index(['paper_id', 'section']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
