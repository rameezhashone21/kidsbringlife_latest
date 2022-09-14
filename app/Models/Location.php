<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_name',
        'address',
        'lat',
        'long',
      ];
      
    
    public function events()
    {
        return $this->hasOne(Event::class);
    }
    
    public function users()
    {
        return $this->hasMany(User::class,'location_id');
    }
}
