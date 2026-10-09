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
        Schema::create('reserveringen', function (Blueprint $table) {
              $table->id();

            $table->foreignId('gebruiker_id')
                ->constrained('gebruikers');

            $table->foreignId('groepsles_id')
                ->constrained('groepslessen');

            $table->enum('status', ['bevestigd', 'geannuleerd'])
                ->default('bevestigd');

            $table->timestamp('aangemeld_op');
            $table->timestamp('geannuleerd_op')->nullable();

            $table->timestamps();

            $table->unique(['gebruiker_id', 'groepsles_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reserveringen');
    }
};
