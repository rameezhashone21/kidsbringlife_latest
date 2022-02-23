<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event_meal extends Model
{
  use HasFactory;

  protected $fillable = [
    'event_id',
    'meal_id',
    'time',
  ];

  public function meals()
  {
      return $this->belongsTo(Meal::class, 'meal_id', 'id');
  }

  // public function events()
  // {
  //     return $this->belongsTo(Event::class, 'event_id', 'id')->select(['id', 'title']);
  // }

}
