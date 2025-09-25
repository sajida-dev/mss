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
        Schema::create('fee_installments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fee_id');
            $table->unsignedBigInteger('student_id');
            $table->decimal('amount', 10, 2);
            $table->date('due_date');
            $table->enum('status', ['unpaid', 'paid'])->default('unpaid');
            $table->dateTime('paid_at')->nullable();
            $table->string('voucher_number')->nullable();
            $table->string('paid_voucher_image')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreign('fee_id')->references('id')->on('fees')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_installments');
    }
};
