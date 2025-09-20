<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();

            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->date('admission_date')->nullable(); // date of entry to this class
            $table->enum('status', ['enrolled', 'promoted', 'repeated', 'transferred', 'left'])->default('enrolled');

            $table->foreignId('previous_class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->text('remarks')->nullable();

            $table->boolean('is_current')->default(false); // Only one true per student

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['student_id', 'academic_year_id']); // 1 record per year
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_enrollments');
    }
};
