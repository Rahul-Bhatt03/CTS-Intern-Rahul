<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable=[
'user_id','date','check_in','check_out','status','total_hours','notes'
    ];

    protected $casts=[
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'total_hours' => 'decimal:2',
        'date' => 'date'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
