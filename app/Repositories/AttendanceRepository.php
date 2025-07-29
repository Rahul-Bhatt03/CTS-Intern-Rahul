<?>
namespace App\Repositories;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceRepository{
    public function getUserAttendance($userId,$date){
        return Attendance::where('user_id',$userId)->whereDate('date',$date)->first();
    }

public function createOrUpdateAttendance($userId,$data){
    $date=$date['date']??now()->toDateString();
    $attendanceData=[
'check_in'=>$data['check_in']??null,
'check_out'=>$data['check_out']??null,
// 'total_hours'=>$data['total_hours']??null,
'status'=>'pending',
'notes'=>$data['notes']??null,
    ];
  
    if(isset($data['check_in'])&&isset($data['check_out'])){
        $checkIn=Carbon::parse($data['check_in']);
        $checkOut=Carbon::parse($data['check_out']);
        $attendanceData['total_hours']=$checkOut->diffInHours($checkIn);        
    }

    return Attendance::updateOrCreate(['user_id'=>$userId,'date'=>$date],$attendanceData);
}

public function getPendingAttendances(){
    return Attendance::with('user')->where('status','pending')->orderBy('date','desc')->paginate(15);
}

public function getSubmittedAttendance(){
    return Attendance::with('user')->where('status','submitted')->orderBy('date','desc')->paginate(15);
}

public function updateAttendanceStatus($id,$status){
    return Attendance::where('id','=',$id)->update(['status'=>$status]);
}

public function bulkUpdateStatus(array $ids,$status){
    return Attendance::whereIn('id',$ids)->update(['status'=>$status]);
}

}
?>