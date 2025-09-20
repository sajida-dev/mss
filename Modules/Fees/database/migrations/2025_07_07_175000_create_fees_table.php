<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('academic_year_id');
            $table->enum('type', ['admission', 'monthly', 'papers', 'installments'])->default('admission');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['unpaid', 'paid', 'cancelled'])->default('unpaid');
            $table->date('due_date');
            $table->dateTime('paid_at')->nullable();
            $table->string('voucher_number')->nullable();
            $table->string('paid_voucher_image')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fees');
    }
};
