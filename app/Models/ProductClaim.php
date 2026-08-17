<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductClaim extends Model
{
    protected $fillable = [
        'serial_number',
        'email',
        'issue_description',
        'urgency_level',
    ];
}
