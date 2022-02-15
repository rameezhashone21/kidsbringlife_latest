<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant_guardian extends Model
{
    use HasFactory;

    protected $fillable = [
        'participant_id',
        'guardian_firstname',
        'guardian_lastname',
        'guardian_role',
        'guardian_address',
      ];
}
