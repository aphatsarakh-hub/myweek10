<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customer';
    protected $primaryKey = 'customer_id';
    public $timestamps = false;

    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'email',
        'address',
    ];

    public function cats()
    {
        return $this->hasMany(Cat::class, 'customer_id', 'customer_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'customer_id', 'customer_id');
    }

    public function accounts()
    {
        return $this->hasMany(User::class, 'customer_id', 'customer_id');
    }
}
