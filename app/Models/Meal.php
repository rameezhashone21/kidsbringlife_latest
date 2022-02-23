<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'value',
      ];

    protected $hidden = [
        'pivot'
    ];

      public function asso_events() {
        return $this->belongsToMany(Event::class, 'event_meals', 'event_id', 'meal_id')
          ->withPivot('created_at')
          ->withTimestamps();
      }
}
