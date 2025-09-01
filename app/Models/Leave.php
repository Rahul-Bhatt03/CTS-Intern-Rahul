<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    use HasFactory;
       protected $fillable = [
        'user_id', 'start_date', 'end_date', 'type', 'status',
        'reason', 'admin_notes', 'approved_by', 'approved_at'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getDurationAttribute()
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    // Check if user is on leave for a specific date
    public static function isUserOnLeave($userId, $date)
    {
        return self::where('user_id', $userId)
                   ->where('status', 'approved')
                   ->where('start_date', '<=', $date)
                   ->where('end_date', '>=', $date)
                   ->exists();
    }
}
