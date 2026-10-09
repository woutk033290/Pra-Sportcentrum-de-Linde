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
        Schema::create('lestypen', function (Blueprint $table) {
            $table->id();

            $table->string('naam')->unique();
            $table->text('beschrijving');
            $table->string('doelgroep');
            $table->string('niveau');
            $table->text('benodigdheden');

            $table->unsignedInteger('duur_minuten');
            $table->unsignedInteger('standaard_capaciteit');

            $table->string('foto_pad')->nullable();
            $table->string('extra_foto_pad')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lestypen');
    }
};
