<?php
namespace App\Services;

use App\Repositories\AttendanceRepository;
use Illuminate\Support\Facades\Auth;

class AttendanceService
{
    protected $attendanceRepository;
    public function __construct(AttendanceRepository $attendanceRepository) {
        $this->attendanceRepository = $attendanceRepository;
    }

    public function checkIn($data){
        $userId=Auth::id();
        $data['check_in']=now();
        $data['date']=now()->toDateString();
        return $this->attendanceRepository->createOrUpdateAttendance($userId,$data);
    }

    public function checkOut($data){
        $userId=Auth::id();
        $data['check_out']=now();
        $data['date']=now()->toDateString();
        return $this->attendanceRepository->createOrUpdateAttendance($userId,$data);
    }

    public function submitAtendance($data){
        $userId=Auth::id();
        $data['status']='submitted';
        $data['date']=now()->toDateString();
        return $this->attendanceRepository->createOrUpdateAttendance($userId,$data);
    }

 public function getUserTodayAttendance()
    {
        return $this->attendanceRepository->getUserAttendance(
            Auth::id(), 
            now()->toDateString()
        );
    }

    public function getPendingAttendances()
    {
        return $this->attendanceRepository->getPendingAttendances();
    }

    public function getSubmittedAttendances()
    {
        return $this->attendanceRepository->getSubmittedAttendances();
    }

    public function updateAttendanceStatus($id, $status)
    {
        return $this->attendanceRepository->updateAttendanceStatus($id, $status);
    }

    public function bulkUpdateStatus(array $ids, $status)
    {
        return $this->attendanceRepository->bulkUpdateStatus($ids, $status);
    }
} 

?>   