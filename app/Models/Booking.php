<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $table = 'booking';
    protected $primaryKey = 'booking_id';
    public $timestamps = false;

    protected $fillable = [
        'customer_id',
        'cat_id',
        'roomtype_id',
        'booking_date',
        'start_date',
        'end_date',
        'total_price',
        'status',
        'services',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function cat()
    {
        return $this->belongsTo(Cat::class, 'cat_id', 'cat_id');
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class, 'roomtype_id', 'roomtype_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'booking_id', 'booking_id');
    }

    public function cancellations()
    {
        return $this->hasMany(Cancellation::class, 'booking_id', 'booking_id');
    }
}
