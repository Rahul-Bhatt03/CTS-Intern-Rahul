<?php
namespace App\Repositories;

use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\WorkSchedule;
use App\Models\Leave;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceRepository
{
    public function getUserAttendance($userId, $date)
    {
        return Attendance::where('user_id', $userId)
                        ->whereDate('date', $date)
                        ->first();
    }

    public function createOrUpdateAttendance($userId, $data)
    {
        $date = $data['date'] ?? now()->toDateString();
        
        $attendanceData = [
            'check_in' => $data['check_in'] ?? null,
            'check_out' => $data['check_out'] ?? null,
            'status' => $data['status'] ?? 'pending',
            'notes' => $data['notes'] ?? null,
            'work_type' => $data['work_type'] ?? 'office',
            'location_data' => $data['location_data'] ?? null,
            'ip_address' => request()->ip(),
            'breaks' => $data['breaks'] ?? null,
        ];

        // Calculate hours and other metrics
        if (isset($data['check_in']) && isset($data['check_out'])) {
            $checkIn = Carbon::parse($data['check_in']);
            $checkOut = Carbon::parse($data['check_out']);
            $totalHours = $checkOut->diffInHours($checkIn, true);
            
            $attendanceData['total_hours'] = $totalHours;
            
            // Calculate break hours
            $breakHours = $this->calculateBreakHours($data['breaks'] ?? []);
            $attendanceData['break_hours'] = $breakHours;
            $attendanceData['productive_hours'] = $totalHours - $breakHours;
        }

        $attendance = Attendance::updateOrCreate(
            ['user_id' => $userId, 'date' => $date],
            $attendanceData
        );

        // Calculate late arrival and overtime
        $this->calculateAttendanceMetrics($attendance);
        
        return $attendance;
    }

    public function calculateBreakHours($breaks)
    {
        if (!is_array($breaks) || empty($breaks)) {
            return 0;
        }

        $totalBreakMinutes = 0;
        foreach ($breaks as $break) {
            if (isset($break['start']) && isset($break['end'])) {
                $start = Carbon::parse($break['start']);
                $end = Carbon::parse($break['end']);
                $totalBreakMinutes += $end->diffInMinutes($start);
            }
        }

        return round($totalBreakMinutes / 60, 2);
    }

    public function calculateAttendanceMetrics($attendance)
    {
        // Get user's schedule for the day
        $dayOfWeek = $attendance->date->format('l');
        $schedule = WorkSchedule::getUserScheduleForDay($attendance->user_id, $dayOfWeek);
        
        if ($schedule && $attendance->check_in) {
            // Calculate late arrival
            $attendance->calculateLateArrival(
                $schedule->start_time, 
                $schedule->grace_period_minutes
            );
            
            // Set expected times
            $attendance->expected_check_in = $schedule->start_time;
            $attendance->expected_check_out = $schedule->end_time;
        }

        // Calculate overtime
        $standardHours = $schedule ? $schedule->expected_hours : AttendanceSetting::getInt('default_work_hours', 8);
        $attendance->calculateOvertimeHours($standardHours);
        
        // Calculate productive hours
        $attendance->calculateProductiveHours();
        
        $attendance->save();
    }

    // Advanced filtering methods
    public function getFilteredAttendances($filters = [], $perPage = 15)
    {
        $query = Attendance::with(['user', 'approvedBy']);

        // Apply filters
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['work_type'])) {
            $query->where('work_type', $filters['work_type']);
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->byDateRange($filters['start_date'], $filters['end_date']);
        }

        if (!empty($filters['is_late'])) {
            $query->late();
        }

        if (!empty($filters['has_overtime'])) {
            $query->withOvertime();
        }

        if (!empty($filters['department'])) {
            $query->whereHas('user', function($q) use ($filters) {
                $q->where('department', $filters['department']);
            });
        }

        return $query->orderBy('date', 'desc')->paginate($perPage);
    }

    // Monthly/Weekly Reports
    public function getAttendanceReport($userId = null, $startDate, $endDate, $groupBy = 'day')
    {
        $query = Attendance::with('user');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $query->byDateRange($startDate, $endDate);

        switch ($groupBy) {
            case 'week':
                $query->select([
                    DB::raw('YEAR(date) as year'),
                    DB::raw('WEEK(date) as week'),
                    DB::raw('COUNT(*) as total_days'),
                    DB::raw('SUM(total_hours) as total_hours'),
                    DB::raw('SUM(overtime_hours) as total_overtime'),
                    DB::raw('SUM(CASE WHEN is_late = 1 THEN 1 ELSE 0 END) as late_days'),
                    DB::raw('AVG(total_hours) as avg_hours'),
                ]);
                $query->groupBy('year', 'week');
                break;

            case 'month':
                $query->select([
                    DB::raw('YEAR(date) as year'),
                    DB::raw('MONTH(date) as month'),
                    DB::raw('COUNT(*) as total_days'),
                    DB::raw('SUM(total_hours) as total_hours'),
                    DB::raw('SUM(overtime_hours) as total_overtime'),
                    DB::raw('SUM(CASE WHEN is_late = 1 THEN 1 ELSE 0 END) as late_days'),
                    DB::raw('AVG(total_hours) as avg_hours'),
                ]);
                $query->groupBy('year', 'month');
                break;

            default: // day
                return $query->orderBy('date', 'desc')->get();
        }

        return $query->orderBy('year', 'desc')->orderBy(DB::raw('COALESCE(month, week)'), 'desc')->get();
    }

    // Analytics data
    public function getAttendanceAnalytics($startDate, $endDate)
    {
        return [
            'total_employees' => $this->getTotalEmployees(),
            'total_present_days' => $this->getTotalPresentDays($startDate, $endDate),
            'total_late_arrivals' => $this->getTotalLateArrivals($startDate, $endDate),
            'total_overtime_hours' => $this->getTotalOvertimeHours($startDate, $endDate),
            'work_type_distribution' => $this->getWorkTypeDistribution($startDate, $endDate),
            'average_hours_per_day' => $this->getAverageHoursPerDay($startDate, $endDate),
            'department_wise_attendance' => $this->getDepartmentWiseAttendance($startDate, $endDate),
            'late_arrival_trends' => $this->getLateArrivalTrends($startDate, $endDate),
        ];
    }

    private function getTotalEmployees()
    {
        return DB::table('users')->where('role', '!=', 'admin')->count();
    }

    private function getTotalPresentDays($startDate, $endDate)
    {
        return Attendance::byDateRange($startDate, $endDate)->count();
    }

    private function getTotalLateArrivals($startDate, $endDate)
    {
        return Attendance::byDateRange($startDate, $endDate)->late()->count();
    }

    private function getTotalOvertimeHours($startDate, $endDate)
    {
        return Attendance::byDateRange($startDate, $endDate)->sum('overtime_hours');
    }

    private function getWorkTypeDistribution($startDate, $endDate)
    {
        return Attendance::byDateRange($startDate, $endDate)
                        ->select('work_type', DB::raw('COUNT(*) as count'))
                        ->groupBy('work_type')
                        ->get();
    }

    private function getAverageHoursPerDay($startDate, $endDate)
    {
        return Attendance::byDateRange($startDate, $endDate)->avg('total_hours');
    }

    private function getDepartmentWiseAttendance($startDate, $endDate)
    {
        return Attendance::byDateRange($startDate, $endDate)
                        ->join('users', 'attendances.user_id', '=', 'users.id')
                        ->select('users.department', DB::raw('COUNT(*) as attendance_count'))
                        ->groupBy('users.department')
                        ->get();
    }

    private function getLateArrivalTrends($startDate, $endDate)
    {
        return Attendance::byDateRange($startDate, $endDate)
                        ->select(
                            DB::raw('DATE(date) as date'),
                            DB::raw('SUM(CASE WHEN is_late = 1 THEN 1 ELSE 0 END) as late_count'),
                            DB::raw('COUNT(*) as total_count')
                        )
                        ->groupBy(DB::raw('DATE(date)'))
                        ->orderBy('date')
                        ->get();
    }

    // Location and GPS related methods
    public function validateLocation($latitude, $longitude)
    {
        $officeCoordinates = AttendanceSetting::getArray('office_coordinates');
        
        if (!$officeCoordinates || !AttendanceSetting::getBool('location_validation')) {
            return true;
        }

        $distance = $this->calculateDistance(
            $latitude, 
            $longitude,
            $officeCoordinates['lat'],
            $officeCoordinates['lng']
        );

        return $distance <= ($officeCoordinates['radius'] ?? 100);
    }

    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371000; // meters
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng/2) * sin($dLng/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }

    // Standard methods (updated)
    public function getPendingAttendances()
    {
        return Attendance::with('user')->where('status', 'pending')->orderBy('date', 'desc')->paginate(15);
    }

    public function getSubmittedAttendances()
    {
        return Attendance::with('user')->where('status', 'submitted')->orderBy('date', 'desc')->paginate(15);
    }

    public function updateAttendanceStatus($id, $status, $adminId = null, $adminNotes = null)
    {
        $data = ['status' => $status];
        
        if ($status === 'approved') {
            $data['approved_at'] = now();
            $data['approved_by'] = $adminId;
        }
        
        if ($adminNotes) {
            $data['admin_notes'] = $adminNotes;
        }

        return Attendance::where('id', $id)->update($data);
    }

    public function bulkUpdateStatus(array $ids, $status, $adminId = null, $adminNotes = null)
    {
        $data = ['status' => $status];
        
        if ($status === 'approved') {
            $data['approved_at'] = now();
            $data['approved_by'] = $adminId;
        }
        
        if ($adminNotes) {
            $data['admin_notes'] = $adminNotes;
        }

        return Attendance::whereIn('id', $ids)->update($data);
    }
}