<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'allergies',
        'ethinic_category',
        'ratial_category',
        'address',
        'guardian',
        'event_id',
        'user_id',
      ];
}
