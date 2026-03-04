<?php 

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Payment extends Model 
{

protected $connection ='mongodb';
protected $collection = 'payments';

public $timestamps = true;
const STATUS_INITIATED = 'initiated';
const STATUS_PENDING = 'pending';
const STATUS_PAID = 'paid';

protected $fillable =[
    'candidature_id',
    'provider_id',
    'amount',
    'status',
    'transaction_id',
    'payment_method',
    'reference',
];

protected $casts = [
    'candidature_id' => 'string',   // ✅ corrigé
    'provider_id' => 'string',
    'amount' => 'int',              // ✅ recommandé (FCFA)
    'paid_at' => 'datetime',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'reference_transaction' => 'string',
];

//relation 

public  function candidature()
{
    return $this->belongsTo(Candidature::class, 'candidature_id');
}


public function provider()
{
    return $this->belongsTo(MobileMoneyProvider::class, 'provider_id');
}

}