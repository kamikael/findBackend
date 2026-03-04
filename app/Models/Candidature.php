<?php
namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Candidature extends Model 
{
  protected $connection = 'mongodb';
  protected $collection = 'candidatures';


  public $timestamps = true;

  const STATUS_PENDING = 'pending';
const STATUS_PAID = 'paid';
const STATUS_CANCELLED = 'cancelled';


protected $fillable = [
         'sector_id',
        'level',
        'student_name',
        'student_email',
        'student_cv_url',
        'partner_name',
        'partner_email',
        'partner_cv_url',
        'status',
        'payment_id',
    ];

    protected $casts = [
        'sector_id' => 'string',
        'payment_id' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    //relatioships

    public function sector() 
    {
        return $this->belongsTo(Sector::class, 'sector_id');
    }

    public function payment() 
    {
        return $this->hasOne(Payment::class, 'candidature_id');
    }

}