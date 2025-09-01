<?php

namespace App\Services;

use App\Models\AttendanceSetting;
use App\Models\Leave;
use App\Repositories\AttendanceRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Nette\Schema\Expect;

class AttendanceService
{
   protected $attendanceRepository;

    public function __construct(AttendanceRepository $attendanceRepository)
    {
        $this->attendanceRepository = $attendanceRepository;
    }

    public function checkIn($data)
    {
        $userId = Auth::id();
        $today = now()->toDateString();

        // Check if user is on leave
        if (Leave::isUserOnLeave($userId, $today)) {
            throw new \Exception('Cannot check in while on approved leave', 400);
        }

        // Validate location if required
        if (!empty($data['latitude']) && !empty($data['longitude'])) {
            if (!$this->attendanceRepository->validateLocation($data['latitude'], $data['longitude'])) {
                if ($data['work_type'] !== 'wfh') {
                    throw new \Exception('Location validation failed. Please check in from office premises or select Work From Home', 400);
                }
            }
        }

        $attendanceData = [
            'check_in' => now(),
            'date' => $today,
            'work_type' => $data['work_type'] ?? 'office',
            'location_data' => [
                'check_in_lat' => $data['latitude'] ?? null,
                'check_in_lng' => $data['longitude'] ?? null,
                'check_in_address' => $data['address'] ?? null,
            ],
            'notes' => $data['notes'] ?? null,
        ];

        return $this->attendanceRepository->createOrUpdateAttendance($userId, $attendanceData);
    }

    public function checkOut($data)
    {
        $userId = Auth::id();
        $today = now()->toDateString();

        // Get existing attendance
        $attendance = $this->attendanceRepository->getUserAttendance($userId, $today);
        if (!$attendance || !$attendance->check_in) {
            throw new \Exception('Please check in first before checking out', 400);
        }

        $attendanceData = [
            'check_out' => now(),
            'date' => $today,
            'location_data' => array_merge($attendance->location_data ?? [], [
                'check_out_lat' => $data['latitude'] ?? null,
                'check_out_lng' => $data['longitude'] ?? null,
                'check_out_address' => $data['address'] ?? null,
            ]),
            'notes' => $data['notes'] ?? $attendance->notes,
        ];

        return $this->attendanceRepository->createOrUpdateAttendance($userId, $attendanceData);
    }

    public function startBreak($data)
    {
        $userId = Auth::id();
        $today = now()->toDateString();

        $attendance = $this->attendanceRepository->getUserAttendance($userId, $today);
        if (!$attendance || !$attendance->check_in) {
            throw new \Exception('Please check in first before starting break', 400);
        }

        $breaks = $attendance->breaks ?? [];
        $breaks[] = [
            'start' => now()->toTimeString(),
            'type' => $data['break_type'] ?? 'general',
            'notes' => $data['notes'] ?? null,
        ];

        return $this->attendanceRepository->createOrUpdateAttendance($userId, [
            'date' => $today,
            'breaks' => $breaks,
        ]);
    }

    public function endBreak($data)
    {
        $userId = Auth::id();
        $today = now()->toDateString();

        $attendance = $this->attendanceRepository->getUserAttendance($userId, $today);
        if (!$attendance || empty($attendance->breaks)) {
            throw new \Exception('No active break found', 400);
        }

        $breaks = $attendance->breaks;
        $lastBreakIndex = count($breaks) - 1;

        if (isset($breaks[$lastBreakIndex]['end'])) {
            throw new \Exception('Last break is already ended', 400);
        }

        $breaks[$lastBreakIndex]['end'] = now()->toTimeString();

        return $this->attendanceRepository->createOrUpdateAttendance($userId, [
            'date' => $today,
            'breaks' => $breaks,
        ]);
    }

    public function submitAttendance($data)
    {
        $userId = Auth::id();
        $data['status'] = 'submitted';
        $data['date'] = $data['date'] ?? now()->toDateString();
        $data['submitted_at'] = now();

        return $this->attendanceRepository->createOrUpdateAttendance($userId, $data);
    }

    public function getUserTodayAttendance()
    {
        return $this->attendanceRepository->getUserAttendance(Auth::id(), now()->toDateString());
    }

    public function getUserAttendanceHistory($filters = [])
    {
        $filters['user_id'] = Auth::id();
        return $this->attendanceRepository->getFilteredAttendances($filters);
    }

    // Admin Methods
    public function getPendingAttendances()
    {
        return $this->attendanceRepository->getPendingAttendances();
    }

    public function getSubmittedAttendances()
    {
        return $this->attendanceRepository->getSubmittedAttendances();
    }

    public function getAllAttendances($filters = [])
    {
        return $this->attendanceRepository->getFilteredAttendances($filters);
    }

    public function updateAttendanceStatus($id, $status, $adminNotes = null)
    {
        $adminId = Auth::id();
        return $this->attendanceRepository->updateAttendanceStatus($id, $status, $adminId, $adminNotes);
    }

    public function bulkUpdateStatus(array $ids, $status, $adminNotes = null)
    {
        $adminId = Auth::id();
        return $this->attendanceRepository->bulkUpdateStatus($ids, $status, $adminId, $adminNotes);
    }

    // Reports and Analytics
    public function getAttendanceReport($userId = null, $startDate, $endDate, $groupBy = 'day')
    {
        return $this->attendanceRepository->getAttendanceReport($userId, $startDate, $endDate, $groupBy);
    }

    public function getAttendanceAnalytics($startDate, $endDate)
    {
        return $this->attendanceRepository->getAttendanceAnalytics($startDate, $endDate);
    }

    public function getMonthlyReport($month = null, $year = null, $userId = null)
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;
        
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $report = $this->getAttendanceReport($userId, $startDate, $endDate, 'day');
        
        return [
            'month' => $month,
            'year' => $year,
            'total_working_days' => $this->getWorkingDaysInMonth($month, $year),
            'attendances' => $report,
            'summary' => $this->calculateMonthlySummary($report),
        ];
    }

    public function getWeeklyReport($weekStart = null, $userId = null)
    {
        $weekStart = $weekStart ? Carbon::parse($weekStart) : now()->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();

        $report = $this->getAttendanceReport($userId, $weekStart, $weekEnd, 'day');
        
        return [
            'week_start' => $weekStart->toDateString(),
            'week_end' => $weekEnd->toDateString(),
            'attendances' => $report,
            'summary' => $this->calculateWeeklySummary($report),
        ];
    }

    public function getLateArrivalReport($startDate, $endDate, $userId = null)
    {
        $filters = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_late' => true,
        ];

        if ($userId) {
            $filters['user_id'] = $userId;
        }

        return $this->attendanceRepository->getFilteredAttendances($filters, 50);
    }

    public function getOvertimeReport($startDate, $endDate, $userId = null)
    {
        $filters = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'has_overtime' => true,
        ];

        if ($userId) {
            $filters['user_id'] = $userId;
        }

        return $this->attendanceRepository->getFilteredAttendances($filters, 50);
    }

    public function getWorkFromHomeReport($startDate, $endDate, $userId = null)
    {
        $filters = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'work_type' => 'wfh',
        ];

        if ($userId) {
            $filters['user_id'] = $userId;
        }

        return $this->attendanceRepository->getFilteredAttendances($filters, 50);
    }

    // Settings Management
    public function getAttendanceSettings()
    {
        return [
            'default_work_hours' => AttendanceSetting::getInt('default_work_hours', 8),
            'overtime_threshold' => AttendanceSetting::getInt('overtime_threshold', 8),
            'late_grace_period' => AttendanceSetting::getInt('late_grace_period', 15),
            'allow_wfh' => AttendanceSetting::getBool('allow_wfh', true),
            'location_validation' => AttendanceSetting::getBool('location_validation', false),
            'office_coordinates' => AttendanceSetting::getArray('office_coordinates'),
        ];
    }

    public function updateAttendanceSettings($settings)
    {
        foreach ($settings as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            AttendanceSetting::set($key, $value);
        }

        return $this->getAttendanceSettings();
    }

    // Helper Methods
    private function getWorkingDaysInMonth($month, $year)
    {
        $start = Carbon::create($year, $month, 1);
        $end = $start->copy()->endOfMonth();
        
        $workingDays = 0;
        while ($start->lte($end)) {
            if (!$start->isWeekend()) {
                $workingDays++;
            }
            $start->addDay();
        }
        
        return $workingDays;
    }

    private function calculateMonthlySummary($attendances)
    {
        $totalDays = $attendances->count();
        $totalHours = $attendances->sum('total_hours');
        $totalOvertime = $attendances->sum('overtime_hours');
        $lateDays = $attendances->where('is_late', true)->count();

        return [
            'total_present_days' => $totalDays,
            'total_hours_worked' => $totalHours,
            'total_overtime_hours' => $totalOvertime,
            'late_arrival_days' => $lateDays,
            'average_hours_per_day' => $totalDays > 0 ? round($totalHours / $totalDays, 2) : 0,
            'attendance_percentage' => $totalDays > 0 ? round(($totalDays / $this->getWorkingDaysInMonth(now()->month, now()->year)) * 100, 2) : 0,
        ];
    }

    private function calculateWeeklySummary($attendances)
    {
        $totalDays = $attendances->count();
        $totalHours = $attendances->sum('total_hours');
        $totalOvertime = $attendances->sum('overtime_hours');
        $lateDays = $attendances->where('is_late', true)->count();

        return [
            'total_present_days' => $totalDays,
            'total_hours_worked' => $totalHours,
            'total_overtime_hours' => $totalOvertime,
            'late_arrival_days' => $lateDays,
            'average_hours_per_day' => $totalDays > 0 ? round($totalHours / $totalDays, 2) : 0,
        ];
    }

    public function validateWorkLocation($latitude, $longitude, $workType)
    {
        if ($workType === 'wfh') {
            return true; // WFH doesn't need location validation
        }

        if (!AttendanceSetting::getBool('location_validation')) {
            return true; // Location validation is disabled
        }

        return $this->attendanceRepository->validateLocation($latitude, $longitude);
    }
}
