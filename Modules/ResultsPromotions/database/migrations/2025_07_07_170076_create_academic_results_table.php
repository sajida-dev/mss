<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('academic_year');
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('section_id')->nullable();

            // Term-wise results (calculated from term_results table)
            $table->decimal('term1_percentage', 5, 2)->nullable();
            $table->decimal('term2_percentage', 5, 2)->nullable();
            $table->decimal('term3_percentage', 5, 2)->nullable();

            // Overall calculations
            $table->decimal('overall_percentage', 5, 2);
            $table->decimal('cumulative_gpa', 4, 2);
            $table->string('final_grade'); // A+, A, B+, B, C, D, F

            // Promotion decision
            $table->enum('promotion_status', ['promoted', 'failed', 'conditional_promotion', 'repeat_class', 'pending'])->default('pending');
            $table->text('promotion_remarks')->nullable();

            // Verification
            $table->boolean('is_verified')->default(false);
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable(); // Final approval
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
            $table->foreign('section_id')->references('id')->on('sections')->onDelete('set null');
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->unique(['student_id', 'academic_year']); // One result per student per year
            $table->index(['student_id', 'academic_year', 'class_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_results');
    }
};
