<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Gebruiker extends Authenticatable
{
    protected $table = 'gebruikers';
    protected $guarded = [];

    protected $hidden = ['password'];

    public function isBeheerder(): bool
    {
        return $this->rol === 'beheerder';
    }
}