<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('class_schools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['class_id', 'school_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('class_schools');
    }
};
