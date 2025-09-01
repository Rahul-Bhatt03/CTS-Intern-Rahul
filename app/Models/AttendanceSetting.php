<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSetting extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'day_of_week',
        'start_time',
        'end_time',
        'grace_period_minutes',
        'expected_hours',
        'is_active'
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'expected_hours' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function getUserScheduleForDay($userId, $dayOfWeek)
    {
        return self::where('user_id', $userId)
            ->where('day_of_week', strtolower($dayOfWeek))
            ->where('is_active', true)
            ->first();
    }
}
