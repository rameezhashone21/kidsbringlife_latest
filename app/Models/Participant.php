<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Participant_attendance_meal;

class Participant extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'allergies',
        'ethinic_category',
        'ratial_category',
        'guardian',
        'event_id',
        'user_id',
      ];

    public function participant_attendance() 
    {
        return $this->hasMany(Participant_attendance::class)->distinct();
    }

    public function participant_meals() 
    {
        return $this->hasMany(Participant_meal::class);
    }
}
