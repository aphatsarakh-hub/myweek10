<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cancellation extends Model
{
    protected $table = 'cancellation';
    protected $primaryKey = 'cancel_id';
    public $timestamps = false;

    protected $fillable = [
        'booking_id',
        'cancel_date',
        'reason',
        'refund_amount',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }
}
