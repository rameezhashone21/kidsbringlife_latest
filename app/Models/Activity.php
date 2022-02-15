<?php

namespace App\Models;

use App\Models\Event;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'start_time',
        'end_time'
    ];

    public function asso_event()
    {
        return $this->belongsToMany(Event::class, 'activities_event', 'activity_id', 'event_id');
    }

}
