<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->string('class');
            $table->string('section')->nullable();
            $table->enum('type', ['admission', 'tuition', 'papers']);
            $table->decimal('amount', 10, 2);
            $table->integer('installments_count')->nullable();
            $table->json('installment_amounts')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['class', 'section', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_structures');
    }
};
