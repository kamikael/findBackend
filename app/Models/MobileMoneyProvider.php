<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MobileMoneyProvider extends Model
{
    use HasFactory;


    protected $connection ='mongodb';
protected $collection = 'mobile_money_providers';


    protected $fillable = [
        'name',
         'code',
        'api_base_url',
        'country_iso',
        'is_active',
    ];

    protected $casts = [
        'name' => 'string',
        'api_base_url' => 'string',
    ];

    /**
     * Relation : 1 Provider → N Paiements
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'provider_id');
    }

}
