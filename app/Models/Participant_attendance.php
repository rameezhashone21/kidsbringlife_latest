<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant_attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'participant_id',
        'date',
        'attendance',
    ];

    protected $hidden = [
        'id',
        'participant_id',
        'created_at',
        'updated_at',
    ];

    public function participants()
    {
        return $this->belongsTo(Participant::class,'participant_id','id');
    }
    
}
