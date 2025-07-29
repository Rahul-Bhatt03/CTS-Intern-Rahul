<?php
namespace App\Services;

use App\Repositories\LunchRepository;
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
        $userId=Auth::id();
        return $this->lunchRepository->createOrUpdateLunchRequest($userId,$date,$hasLunch);
    }

    public function getUserTodayLunchRequest(){
        return $this->lunchRepository->getUserLunchRequest(Auth::id(),now()->toDateString());
    }

    public function getLunchRequests($status=null){
        return $this->lunchRepository->getLunchRequest($status);
    }

    public function updateRequestStatus($id,$status){
        return $this->lunchRepository->updateLunchRequestStatus($id,$status);
    }
}


?>