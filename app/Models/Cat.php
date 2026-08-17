<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cat extends Model
{
    protected $table = 'cat';
    protected $primaryKey = 'cat_id';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'breed',
        'age',
        'gender',
        'medical_history',
        'photo',
        'customer_id',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'cat_id', 'cat_id');
    }
}
