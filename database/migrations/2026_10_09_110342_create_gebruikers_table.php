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
        Schema::create('gebruikers', function (Blueprint $table) {
             $table->id();

            $table->string('voornaam');
            $table->string('achternaam');
            $table->string('email')->unique();
            $table->string('telefoonnummer');
            $table->date('geboortedatum')->nullable();

            $table->string('password')->nullable();
            $table->enum('rol', ['lid', 'trainer', 'beheerder'])
                ->default('lid');

            $table->text('trainer_bio')->nullable();
            $table->string('profielfoto_pad')->nullable();

            $table->timestamp('voorwaarden_akkoord_op')->nullable();
            $table->timestamp('geactiveerd_op')->nullable();
            $table->timestamp('verwijderd_op')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gebruikers');
    }
};
