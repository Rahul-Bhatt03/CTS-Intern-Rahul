<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        "phone_number",
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function lunchRequests()
    {
        return $this->hasMany(LunchRequest::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
    public function isAdmin()
    {
        return $this->role === 'admin';
    }
    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }
    public function workSchedules()
    {
        return $this->hasMany(WorkSchedule::class);
    }
    public function getScheduleForDay($dayOfWeek)
    {

        return $this->workSchedules()
            ->where('day_of_week', strtolower($dayOfWeek))
            ->where('is_active', true)
            ->first();
    }
}
