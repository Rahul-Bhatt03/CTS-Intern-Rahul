<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'check_in',
        'check_out',
        'status',
        'total_hours',
        'notes',
        'work_type',
        'location_data',
        'is_late',
        'late_minutes',
        'regular_hours',
        'overtime_hours',
        'expected_check_in',
        'expected_check_out',
        'breaks',
        'break_hours',
        'productive_hours',
        'ip_address',
        'admin_notes',
        'submitted_at',
        'approved_at',
        'approved_by'
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'date' => 'date',
        'total_hours' => 'decimal:2',
        'regular_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'break_hours' => 'decimal:2',
        'productive_hours' => 'decimal:2',
        'location_data' => 'array',
        'breaks' => 'array',
        'is_late' => 'boolean',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    // calculate if employee is late 
    public function calculateLateArrival($expectedTime, $gracePeriod = 60)
    {
        if (!$this->cheeck_in || $expectedTime) {
            return;
        }

        $expected = Carbon::parse($this->date->format('Y-m-d') . ' ' . $expectedTime);
        $actual = Carbon::parse($this->check_in);

        $lateMinutes = $actual->diffInMinutes($expected, false);

        if ($lateMinutes > $gracePeriod) {
            $this->is_late = true;
            $this->late_minutes = $lateMinutes;
        }
    }

    // Calculate overtime hours
    public function calculateOvertimeHours($standardHours = 8)
    {
        if ($this->total_hours <= $standardHours) {
            $this->regular_hours = $this->total_hours;
            $this->overtime_hours = 0;
        } else {
            $this->regular_hours = $standardHours;
            $this->overtime_hours = $this->total_hours - $standardHours;
        }
    }

    // Calculate productive hours (total - breaks)
    public function calculateProductiveHours()
    {
        $this->productive_hours = $this->total_hours - $this->break_hours;
    }

    // Scopes for filtering
    public function scopeLate($query)
    {
        return $query->where('is_late', true);
    }

    public function scopeByWorkType($query, $type)
    {
        return $query->where('work_type', $type);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeWithOvertime($query)
    {
        return $query->where('overtime_hours', '>', 0);
    }
}
