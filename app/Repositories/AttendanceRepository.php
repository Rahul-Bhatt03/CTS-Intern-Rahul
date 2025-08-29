<?php

namespace App\Repositories;

use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceRepository
{
    public function getUserAttendance($userId, $date)
    {
        return Attendance::where('user_id', $userId)->whereDate('date', $date)->first();
    }

    public function createOrUpdateAttendance($userId, $data)
    {
        $date = $data['date'] ?? now()->toDateString();
        $attendanceData = [
            'check_in' => $data['check_in'] ?? null,
            'check_out' => $data['check_out'] ?? null,
            // 'total_hours'=>$data['total_hours']??null,
            'status' => $date['status']??'pending',
            'notes' => $data['notes'] ?? null,
            'work_type'=>$data['work_type']??'office',
            'location_data'=>$data['location_data']?? null,
            'ip_address'=>request()->ip(),
            'breaks'=>$data['breaks']?? null,
        ];
// calculating hours and other matrices 
        if (isset($data['check_in']) && isset($data['check_out'])) {
            $checkIn = Carbon::parse($data['check_in']);
            $checkOut = Carbon::parse($data['check_out']);
            $totalHours=$checkOut->diffInHours($checkIn,true);
            $attendanceData['total_hours'] = $totalHours;

            // calculate break hours
            $breakHours=$this->calculateBreakHours($data['breaks']??[]);
        }

        return Attendance::updateOrCreate(['user_id' => $userId, 'date' => $date], $attendanceData);
    }

    public function getPendingAttendances()
    {
        return Attendance::with('user')->where('status', 'pending')->orderBy('date', 'desc')->paginate(15);
    }

    public function getSubmittedAttendances()
    {
        return Attendance::with('user')->where('status', 'submitted')->orderBy('date', 'desc')->paginate(15);
    }

    public function updateAttendanceStatus($id, $status)
    {
        return Attendance::where('id', '=', $id)->update(['status' => $status]);
    }

    public function bulkUpdateStatus(array $ids, $status)
    {
        return Attendance::whereIn('id', $ids)->update(['status' => $status]);
    }
}
