<?php

namespace App\Models;

use Database\Factories\GebruikerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Gebruiker extends Authenticatable
{
    /** @use HasFactory<GebruikerFactory> */
    use HasFactory;

    protected $table = 'gebruikers';

    protected $guarded = [];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function isBeheerder(): bool
    {
        return $this->rol === 'beheerder';
    }

    /**
     * Maakt het e-mailadres vrij van verwijderde accounts (de kolom email is uniek),
     * zodat het adres opnieuw gebruikt kan worden.
     */
    public static function geefEmailVrij(string $email): void
    {
        if ($email === '') {
            return;
        }

        static::where('email', $email)
            ->whereNotNull('verwijderd_op')
            ->get()
            ->each(function (Gebruiker $gebruiker) {
                $gebruiker->update([
                    'email' => mb_substr("verwijderd-{$gebruiker->id}-{$gebruiker->email}", 0, 255),
                ]);
            });
    }
}
