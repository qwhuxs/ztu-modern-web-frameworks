<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Створює таблицю diagnoses.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('diagnoses', function (Blueprint $table) {
            $table->id();
            // Зв'язок: діагноз поставлено на конкретному прийомі.
            $table->foreignId('appointment_id')->constrained('appointments')->restrictOnDelete();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Видаляє таблицю diagnoses.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('diagnoses');
    }
};