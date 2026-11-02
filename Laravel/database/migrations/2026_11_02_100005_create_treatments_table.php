<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Створює таблицю treatments.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('treatments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diagnosis_id')->constrained('diagnoses')->restrictOnDelete();
            $table->text('prescription');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Видаляє таблицю treatments.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('treatments');
    }
};