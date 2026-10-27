<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lestype extends Model
{
    protected $table = 'lestypen';
    protected $guarded = [];

    public function groepslessen()
    {
        return $this->hasMany(Groepsles::class);
    }
}