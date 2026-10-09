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
        Schema::create('groepslessen', function (Blueprint $table) {
          $table->id();

            $table->foreignId('lestype_id')
                ->constrained('lestypen');

            $table->foreignId('trainer_id')
                ->constrained('gebruikers');

            $table->foreignId('locatie_id')
                ->constrained('locaties');

            $table->foreignId('lesreeks_id')
                ->nullable()
                ->constrained('lesreeksen');

            $table->string('naam');
            $table->text('beschrijving');
            $table->string('foto_pad')->nullable();

            $table->dateTime('starttijd');
            $table->dateTime('eindtijd');
            $table->unsignedInteger('maximaal_aantal_deelnemers');

            $table->enum('status', ['gepland', 'geannuleerd'])
                ->default('gepland');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groepslessen');
    }
};
