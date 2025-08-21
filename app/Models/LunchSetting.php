<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LunchSetting extends Model
{
    use HasFactory;

    protected $fillable=[
        'start_time',
        'end_time',
        'is_active'
    ];

    protected $casts=[
        'start_time'=>'datetime:H:i',
        'end_time'=>'datetime:H:i',
        'is_active'=>'boolean'
    ];
}
