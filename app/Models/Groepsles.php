<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Groepsles extends Model
{
    protected $table = 'groepslessen';
    protected $guarded = [];
    protected $casts = [
        'starttijd' => 'datetime',
        'eindtijd'  => 'datetime',
    ];

    public function lestype()
    {
        return $this->belongsTo(Lestype::class);
    }

    public function trainer()
    {
        return $this->belongsTo(Gebruiker::class, 'trainer_id');
    }

    public function locatie()
    {
        return $this->belongsTo(Locatie::class);
    }

    /** Geeft een raster terug: $grid[dagdeel][dagIndex 0-6] = [lessen] */
    public static function weekGrid(Carbon $start, ?int $lestypeId = null): array
    {
        $lessen = static::with('lestype')
            ->where('status', 'gepland')
            ->whereBetween('starttijd', [$start->copy()->startOfDay(), $start->copy()->addDays(6)->endOfDay()])
            ->when($lestypeId, fn ($q) => $q->where('lestype_id', $lestypeId))
            ->orderBy('starttijd')
            ->get();

        $grid = [];
        foreach ($lessen as $les) {
            $uur  = $les->starttijd->hour;
            $deel = $uur < 12 ? 'ochtend' : ($uur < 18 ? 'middag' : 'avond');
            $grid[$deel][$les->starttijd->dayOfWeekIso - 1][] = $les;
        }

        return $grid;
    }
}