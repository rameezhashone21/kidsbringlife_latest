<?php

namespace App\Models;

use App\Models\User;
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

    public function activities()
    {
        return $this->belongsToMany(Activity::class, 'activities_event', 'event_id', 'activity_id');
    }
}
