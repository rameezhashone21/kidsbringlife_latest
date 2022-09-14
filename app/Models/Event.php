<?php

namespace App\Models;

use App\Models\User;
use App\Models\Location;
use App\Models\Participant;
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
        'location_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'pivot'
    ];

    public function users() 
    {
        return $this->belongsToMany(User::class, 'event_users', 'event_id', 'user_id');
    }
    
    public function locations()
    {
        return $this->belongsTo(Location::class,'location_id', 'id');
    }

    public function meals() 
    {
        return $this->belongsToMany(Meal::class, 'event_meals', 'event_id', 'meal_id');
    }
    
    public function event_days() 
    {
        return $this->hasMany(Event_day::class, 'event_id', 'id');
    }
    public function meal_participant() 
    {
        return $this->belongsToMany(Meal::class, 'event_meals_participants', 'event_id', 'meal_type');
    }


    public function event_meals() 
    {
        return $this->hasMany(Event_meal::class);
          //     return $this->belongsTo(Event_meal::class, 'event_id', 'id')->select(['id', 'title']);

    }
    


    public function activities()
    {
        return $this->belongsToMany(Activity::class, 'activities_event', 'event_id', 'activity_id');
    }
}
