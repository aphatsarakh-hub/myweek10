<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $table = 'staff';
    protected $primaryKey = 'staff_id';
    public $timestamps = false;

    protected $fillable = [
        'first_name',
        'last_name',
        'gender',
        'birth_date',
        'position',
        'phone',
        'email',
        'address',
        'hire_date',
        'salary',
        'status',
        'image',
    ];

    public function accounts()
    {
        return $this->hasMany(User::class, 'staff_id', 'staff_id');
    }
}
