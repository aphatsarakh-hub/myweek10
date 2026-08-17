<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    protected $table = 'roomtype';
    protected $primaryKey = 'roomtype_id';
    public $timestamps = false;

    protected $fillable = [
        'type_name',
        'room_number',
        'description',
        'price_per_night',
        'capacity',
        'status',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'roomtype_id', 'roomtype_id');
    }
}
