<?php
namespace App\Services;

use App\Repositories\LunchRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class LunchService {
    protected $lunchRepository;
    public function __construct(lunchRepository $lunchRepository) {
        $this->lunchRepository = $lunchRepository;
    }

    public function getCurrentSettings(){
        return $this->lunchRepository->getCurrentSettings();
    }
    public function updateSettings(array $data){
        return $this->lunchRepository->updateSettings($data);
    }
    public function submitLunchRequest($hasLunch,$date=null){
        $date=$date??now()->toDateString();
           // Validate date is not in the past (except today)
        if (Carbon::parse($date)->isPast() && !Carbon::parse($date)->isToday()) {
            throw new \Exception('Cannot submit lunch request for past dates', 400);
        }
        
        // Validate date is not too far in the future (e.g., 30 days)
        if (Carbon::parse($date)->gt(now()->addDays(30))) {
            throw new \Exception('Cannot submit lunch request more than 30 days in advance', 400);
        }
        $userId=Auth::id();
        return $this->lunchRepository->createOrUpdateLunchRequest($userId,$date,$hasLunch);
    }

    public function getUserTodayLunchRequest(){
        return $this->lunchRepository->getUserLunchRequest(Auth::id(),now()->toDateString());
    }

    public function getLunchRequests($status = null, $date = null, $userId = null) {
        return $this->lunchRepository->getLunchRequest($status, $date, $userId);
    }

    public function updateRequestStatus($id, $status) {
        return $this->lunchRepository->updateLunchRequestStatus($id, $status);
    }
     public function getUserLunchHistory($startDate = null, $endDate = null) {
        $startDate = $startDate ?? now()->subMonth()->toDateString();
        $endDate = $endDate ?? now()->toDateString();
        
        return $this->lunchRepository->getUserLunchHistory(Auth::id(), $startDate, $endDate);
    }
     public function deleteLunchRequest($id) {
        $userId = Auth::id();
        $request = $this->lunchRepository->deleteLunchRequest($id, $userId);
        
        if (!$request) {
            return false;
        }
        
        // Only allow deletion of future requests or today's request
        if (Carbon::parse($request->date)->isPast() && !Carbon::parse($request->date)->isToday()) {
            throw new \Exception('Cannot delete past lunch requests', 400);
        }
        
        return $this->lunchRepository->deleteLunchRequest($id, $userId);
    }
}


?>