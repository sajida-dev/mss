<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fee_id');
            $table->unsignedBigInteger('fee_installment_id')->nullable();
            $table->enum('type', ['tuition', 'library', 'security', 'admission', 'sports', 'papers', 'transport'])->default('tuition');
            $table->decimal('amount', 10, 2);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('fee_installment_id')->references('id')->on('fee_installments')->onDelete('cascade');
            $table->foreign('fee_id')->references('id')->on('fees')->onDelete('cascade');
            $table->index('fee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_items');
    }
};
