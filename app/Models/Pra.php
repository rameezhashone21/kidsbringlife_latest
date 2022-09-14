<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pra extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'invoice_number',
        'POSID',
        'USIN',
    ];

}
