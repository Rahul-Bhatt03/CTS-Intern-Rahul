<?php
namespace App\Repositories;

use App\Models\LunchRequest;
use App\Models\LunchSetting;
use Carbon\Carbon;

class LunchRepository{
    public function getCurrentSettings(){
        return LunchSetting::where('is_active', true)->first();
    }

    public function updateSettings(array $data){
        //deactivate all other settings
        LunchSetting::query()->update(['is_active' => false]);
        return LunchSetting::create([
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'is_active' => true
        ]);
    }

    public function getUserLunchRequest($userId ,$data){
        return LunchRequest::where('user_id',$userId)->whereDate('date',$date)->first();
    }

    public function createOrUpdateLunchRequest($userId, $data,$hasLunch){
        return LunchRequest::updateOrCreate(
            ['user_id'=>$userId,'date'=>$date],
            [
                'has_lunch'=>$hasLunch
                ,'status'=>'pending'
            ]
            );
    }

    public function getLunchRequest($status=null){
        $query=LunchRequest::with('user')->orderBy('date','desc');
        if($status){
            $query->where('status','=',$status);
        }
        return $query->paginate(15);
    }

    public function updateLunchRequestStatus($id,$status){
        return LunchRequest::where('id','=',$id)->update(['status'=>$status]);
    }
}

?>