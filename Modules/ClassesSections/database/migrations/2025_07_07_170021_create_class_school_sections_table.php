<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('class_school_sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('class_school_id');
            $table->unsignedBigInteger('section_id');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['class_school_id', 'section_id']);

            $table->foreign('class_school_id')->references('id')->on('class_schools')->onDelete('cascade');
            $table->foreign('section_id')->references('id')->on('sections')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('class_school_sections');
    }
};
