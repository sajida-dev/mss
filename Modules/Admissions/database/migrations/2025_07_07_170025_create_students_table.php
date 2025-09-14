<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    const GENDER_VALUES = ['male', 'female', 'other'];
    const CLASS_SHIFT_VALUES = ['morning', 'evening', 'other'];
    const INCLUSIVE_VALUES = ['no disability', 'physical', 'visual', 'hearing', 'intellectual', 'other'];
    const RELIGION_VALUES = ['islam', 'christianity', 'hinduism', 'other'];
    const FATHER_PROFESSION_VALUES = ['unemployed', 'private/self employed', 'government', 'other'];
    const JOB_TYPE_VALUES = ['private/self employed', 'government', 'other'];
    const EDUCATION_VALUES = ['none', 'primary', 'middle', 'matric', 'intermediate', 'graduate', 'post graduate'];
    const MOTHER_PROFESSION_VALUES = ['house wife', 'private/self employed', 'government', 'other'];
    const INCOME_LEVELS = [
        'income level between rs. 0 - 20,000',
        'income level between rs. 20,001 - 27,000',
        'income level between rs. 27,001 - 35,000',
        'income level between rs. 35,001 - 50,000',
        'income level above rs. 50,000',
    ];
    const MOTHER_INCOMES = [
        'none',
        'income level between rs. 0 - 20,000',
        'income level between rs. 20,001 - 27,000',
        'income level between rs. 27,001 - 35,000',
        'income level between rs. 35,001 - 50,000',
        'income level above rs. 50,000',
    ];

    public function up(): void
    {

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('section_id')->nullable()->constrained('sections')->onDelete('set null');
            $table->enum('nationality', [
                'pakistan',
                'india',
                'bangladesh',
                'afghanistan',
                'china',
                'saudi arabia',
                'united arab emirates',
                'united states',
                'united kingdom',
                'canada',
                'australia',
                'turkey',
                'iran',
                'indonesia',
                'malaysia',
                'egypt',
                'south africa',
                'germany',
                'france',
                'italy',
                'other',
            ]);
            $table->string('registration_number')->unique();
            $table->string('name');
            $table->string('b_form_number')->unique();
            $table->date('admission_date');
            $table->date('date_of_birth');
            $table->enum('gender', self::GENDER_VALUES);
            $table->enum('class_shift', self::CLASS_SHIFT_VALUES);
            $table->string('previous_school')->nullable();
            $table->enum('inclusive', self::INCLUSIVE_VALUES);
            $table->string('other_inclusive_type')->nullable();
            $table->enum('religion', self::RELIGION_VALUES);
            $table->boolean('is_bricklin')->default(false);
            $table->boolean('is_orphan')->default(false);
            $table->boolean('is_qsc')->default(false);
            $table->string('profile_photo_path')->nullable();
            // Family Information
            $table->string('father_name');
            $table->string('guardian_name')->nullable();
            $table->string('father_cnic');
            $table->string('mother_cnic')->nullable();
            $table->enum('father_profession', self::FATHER_PROFESSION_VALUES);
            $table->integer('no_of_children')->nullable();
            $table->enum('job_type', self::JOB_TYPE_VALUES)->nullable();
            $table->enum('father_education', self::EDUCATION_VALUES);
            $table->enum('mother_education', self::EDUCATION_VALUES)->nullable();
            $table->enum('mother_profession', self::MOTHER_PROFESSION_VALUES)->nullable();
            $table->enum('father_income', self::INCOME_LEVELS);
            $table->enum('mother_income', self::MOTHER_INCOMES)->nullable();
            $table->enum('household_income', self::INCOME_LEVELS);
            $table->string('permanent_address');
            $table->string('phone_no')->nullable();
            $table->string('mobile_no');
            $table->enum('status', ['applicant', 'admitted', 'rejected'])->default('applicant');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
