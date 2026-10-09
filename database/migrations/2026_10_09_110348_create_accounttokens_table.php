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
        Schema::create('accounttokens', function (Blueprint $table) {
                      $table->id();

            $table->foreignId('gebruiker_id')
                ->constrained('gebruikers');

            $table->string('token_hash')->unique();
            $table->enum('doel', ['activatie', 'reset']);

            $table->timestamp('vervalt_op');
            $table->timestamp('gebruikt_op')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounttokens');
    }
};
