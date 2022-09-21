<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('users', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('email')->unique();
      $table->string('phone_number')->nullable();
      $table->timestamp('email_verified_at')->nullable();
      $table->string('password');
      $table->integer('location_id')->nullable();
      $table->string('signature')->nullable();
      $table->string('profile_photo')->nullable();
      $table->string('assigned_to_event')->default(0);
      $table->tinyInteger('status');
      $table->rememberToken();
      
      $table->timestamps();
      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('users');
  }
}
