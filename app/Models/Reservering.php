<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservering extends Model
{
    protected $table = 'reserveringen';
    protected $guarded = [];

    protected $casts = [
        'aangemeld_op'   => 'datetime',
        'geannuleerd_op' => 'datetime',
    ];

    public function gebruiker()
    {
        return $this->belongsTo(Gebruiker::class);
    }

    public function groepsles()
    {
        return $this->belongsTo(Groepsles::class, 'groepsles_id');
    }
}