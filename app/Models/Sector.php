<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Sector extends Model
{
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_COMPLETED = 'completed';

    protected $connection = 'mongodb';
    protected $collection = 'sectors';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'description',
        'domain_id',
        'status',
        'level',
        'total_slots',
        'available_slots',
    ];

    protected $casts = [
        'domain_id' => 'string',
        'total_slots' => 'integer',
        'available_slots' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Sector $sector) {
            $totalSlots = max(0, (int) ($sector->total_slots ?? 0));
            $availableSlots = (int) ($sector->available_slots ?? $totalSlots);

            if ($availableSlots > $totalSlots) {
                $availableSlots = $totalSlots;
            }

            if ($availableSlots < 0) {
                $availableSlots = 0;
            }

            $sector->total_slots = $totalSlots;
            $sector->available_slots = $availableSlots;
            $sector->status = $availableSlots > 0
                ? self::STATUS_AVAILABLE
                : self::STATUS_COMPLETED;
        });
    }

    public function candidatures()
    {
        return $this->hasMany(Candidature::class, 'sector_id');
    }

    public function domain()
    {
        return $this->belongsTo(Domain::class, 'domain_id');
    }
}
