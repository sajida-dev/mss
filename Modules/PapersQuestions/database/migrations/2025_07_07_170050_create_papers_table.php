<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('papers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('section_id')->nullable();
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('subject_id'); // Add subject_id field
            $table->string('title');
            $table->boolean('published')->default(false);
            $table->integer('total_marks')->default(0);
            $table->integer('time_duration')->default(120); // in minutes
            $table->text('instructions')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('section_id')->references('id')->on('sections')->onDelete('set null');
            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade'); // Add foreign key
            $table->index(['class_id', 'section_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('papers');
    }
};
