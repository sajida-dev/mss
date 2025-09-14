<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // for single subject of single class
        Schema::create('exam_paper', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exam_id');
            $table->unsignedBigInteger('paper_id');
            $table->unsignedBigInteger('subject_id');
            $table->date('exam_date'); // specific date for this subject
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('total_marks')->default(100);
            $table->integer('passing_marks')->default(40);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('exam_id')->references('id')->on('exams')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('paper_id')->references('id')->on('papers')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('restrict')->onUpdate('cascade');
            // $table->unique(['exam_id', 'paper_id']); // one paper per exam
            $table->index(['exam_id', 'exam_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_paper');
    }
};
