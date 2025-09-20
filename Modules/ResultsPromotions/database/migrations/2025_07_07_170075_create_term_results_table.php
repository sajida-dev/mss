<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('term_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('exam_id'); // References the term exam
            $table->foreignId('exam_type_id')->constrained('exam_types');
            $table->integer('total_subjects');
            $table->decimal('obtained_marks', 10, 2);
            $table->decimal('total_marks', 10, 2);
            $table->decimal('overall_percentage', 5, 2);
            $table->integer('subjects_passed');
            $table->integer('subjects_failed');
            $table->enum('term_status', ['pass', 'fail', 'pending'])->default('pending');
            $table->decimal('grade_points', 4, 2)->nullable(); // GPA calculation
            $table->string('grade')->nullable(); // A+, A, B+, B, C, D, F
            $table->text('remarks')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('exam_id')->references('id')->on('exams')->onDelete('cascade');
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
            $table->unique(['student_id', 'exam_id']); // One term result per student per exam
            $table->index(['student_id', 'academic_year_id', 'exam_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('term_results');
    }
};
