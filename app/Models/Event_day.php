<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event_day extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'event_id',
        'days',
    ];

   
}
