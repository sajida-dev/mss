<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('section_id')->nullable();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->date('date');

            $table->enum('status', ['present', 'absent', 'late', 'half_day'])->default('present');
            $table->text('remarks')->nullable();

            $table->unsignedBigInteger('marked_by')->nullable();
            $table->unsignedBigInteger('teacher_id')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('set null');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
            $table->foreign('section_id')->references('id')->on('sections')->onDelete('cascade');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('marked_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('set null');

            // Unique constraint to prevent duplicate attendance records
            $table->unique(['student_id', 'class_id', 'date'], 'unique_student_class_date');

            // Indexes for better performance
            $table->index(['class_id', 'date']);
            $table->index(['school_id', 'date']);
            $table->index(['student_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
