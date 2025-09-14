<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('class_subject_teacher', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->unique(['class_id', 'subject_id', 'teacher_id'], 'class_subject_teacher_unique');
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down()
    {
        Schema::dropIfExists('class_subject_teacher');
    }
};
