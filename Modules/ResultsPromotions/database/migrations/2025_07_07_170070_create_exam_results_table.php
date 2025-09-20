<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // subject result definition for single subject
        Schema::create('exam_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exam_paper_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('academic_year_id');
            $table->decimal('obtained_marks', 8, 2);
            $table->decimal('total_marks', 8, 2);
            $table->decimal('percentage', 5, 2)->nullable();
            $table->enum('status', ['pass', 'fail', 'absent'])->default('pass');
            $table->enum('promotion_status', ['promoted', 'failed', 'pending'])->default('pending');
            $table->text('remarks')->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('cascade');
            $table->foreign('exam_paper_id')->references('id')->on('exam_paper')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->unique(['exam_paper_id', 'student_id']); // one result per student per exam paper
            $table->index(['exam_paper_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_results');
    }
};
