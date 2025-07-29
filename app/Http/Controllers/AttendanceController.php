<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceRequest;
use App\Services\AttendanceService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
        $this->middleware('auth');
    }

    public function checkIn()
    {
        $attendance = $this->attendanceService->checkIn([]);
        return response()->json($attendance);
    }

    public function checkOut()
    {
        $attendance = $this->attendanceService->checkOut([]);
        return response()->json($attendance);
    }

    public function submitAttendance(AttendanceRequest $request)
    {
        $attendance = $this->attendanceService->submitAttendance($request->validated());
        return response()->json($attendance);
    }

    public function getUserAttendance()
    {
        $attendance = $this->attendanceService->getUserTodayAttendance();
        return response()->json($attendance);
    }

    public function getPendingAttendances()
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $attendances = $this->attendanceService->getPendingAttendances();
        return response()->json($attendances);
    }

    public function getSubmittedAttendances()
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $attendances = $this->attendanceService->getSubmittedAttendances();
        return response()->json($attendances);
    }

    public function updateAttendanceStatus(Request $request, $id)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate(['status' => 'required|in:approved,rejected']);
        
        $updated = $this->attendanceService->updateAttendanceStatus($id, $request->status);
        return response()->json(['success' => $updated]);
    }

    public function bulkUpdateStatus(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:attendances,id',
            'status' => 'required|in:approved,rejected'
        ]);
        
        $updated = $this->attendanceService->bulkUpdateStatus($request->ids, $request->status);
        return response()->json(['success' => $updated]);
    }
}


?>
