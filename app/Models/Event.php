<?php

namespace App\Models;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'event_name',
        'details',
        'start_date',
        'end_date',
        'meal_type',
      ];

    public function activities()
    {
        return $this->belongsToMany(Activity::class, 'activities_event', 'event_id', 'activity_id');
    }
}
