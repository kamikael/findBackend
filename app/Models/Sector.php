<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Sector extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'sectors';


    public $timestamps = true;          // ✅ recommandé (cohérent avec casts)

    protected $fillable = [
        'name',
        'description',
        'total_slots',
        'available_slots',
    ];

    protected $casts = [
        'total_slots' => 'integer',
        'available_slots'=> 'integer',
        'created_at'=> 'datetime',
        'updated_at'=> 'datetime',
    ];

    public function candidatures()
    {
        return $this->hasMany(Candidature::class, 'sector_id');
    }
}