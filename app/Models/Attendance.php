<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'type',
        'latitude',
        'longitude',
        'in_range',
    ];

    public function user()
    {
        return $this->belongsTo(Employee::class, 'user_id');
    }
}
