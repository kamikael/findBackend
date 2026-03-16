<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Domain extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'domains';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'description',
    ];

    public function sectors()
    {
        return $this->hasMany(Sector::class, 'domain_id');
    }
}
