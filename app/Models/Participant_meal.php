<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant_meal extends Model
{
    use HasFactory;

    protected $fillable = [
        'participant_id',
        'date',
        'meal_type',
        'meal_taken',
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function participants()
    {
        return $this->belongsTo(Participant::class,'participant_id','id');
    }
    
}
