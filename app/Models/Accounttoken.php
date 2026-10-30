<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Accounttoken extends Model
{
    protected $table = 'accounttokens';
    protected $guarded = [];

    const UPDATED_AT = null;

    protected $casts = [
        'vervalt_op'  => 'datetime',
        'gebruikt_op' => 'datetime',
    ];

    public function gebruiker()
    {
        return $this->belongsTo(Gebruiker::class);
    }
}