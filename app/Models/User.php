<?php

namespace App\Models;

use App\Models\Role;
use App\Traits\HasRoleAndPermission;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
  use HasFactory, Notifiable, SoftDeletes, HasRoleAndPermission, HasApiTokens;

  // Table Name
  protected $table = 'users';

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'name',
    'email',
    'password',
    'profile_photo',
    'status',
  ];

  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */
  protected $hidden = [
    'password',
    'remember_token',
    'pivot',
  ];

  /**
   * The attributes that should be cast to native types.
   *
   * @var array
   */
  protected $casts = [
    'email_verified_at' => 'datetime',
  ];

  public function roles()
  {
    return $this->belongsToMany(Role::class, 'role_users');
  }

  public function asso_events() {
    return $this->belongsToMany(Event::class, 'event_users', 'event_id', 'user_id')
      ->withPivot('created_at')
      ->withTimestamps();
  }
}
