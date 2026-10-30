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
        'eindtijd' => 'datetime',
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
    public function reserveringen()
    {
        return $this->hasMany(Reservering::class, 'groepsles_id');
    }

    /** Geeft een raster terug: $grid[dagdeel][dagIndex 0-6] = [lessen] */
    public static function weekGrid(
        Carbon $start,
        ?int $lestypeId = null,
        ?int $trainerId = null,
        ?int $dag = null
    ): array {
        $lessen = static::with(['lestype', 'trainer', 'locatie'])
            ->withCount(['reserveringen as bevestigd_aantal' => fn($q) => $q->where('status', 'bevestigd')])
            ->whereBetween('starttijd', [$start->copy()->startOfDay(), $start->copy()->addDays(6)->endOfDay()])
            ->when($lestypeId, fn($q) => $q->where('lestype_id', $lestypeId))
            ->when($trainerId, fn($q) => $q->where('trainer_id', $trainerId))
            ->orderBy('starttijd')
            ->get();

        $grid = [];
        foreach ($lessen as $les) {
            $dagIndex = $les->starttijd->dayOfWeekIso - 1;

            if ($dag !== null && $dagIndex !== $dag - 1) {
                continue;
            }

            $uur = $les->starttijd->hour;
            $deel = $uur < 12 ? 'ochtend' : ($uur < 18 ? 'middag' : 'avond');
            $grid[$deel][$dagIndex][] = $les;
        }

        return $grid;
    }
}