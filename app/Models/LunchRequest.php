<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LunchRequest extends Model
{
    use HasFactory;

    protected $fillable=[
        'user_id','date','has_lunch','status'
    ];

    protected $casts = [
        'date' => 'date',
        'has_lunch' => 'boolean',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
