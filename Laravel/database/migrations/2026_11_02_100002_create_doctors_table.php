<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Створює таблицю doctors.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('specialty', 100);
            $table->string('phone', 20);
            $table->timestamps();
        });
    }

    /**
     * Видаляє таблицю doctors.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};