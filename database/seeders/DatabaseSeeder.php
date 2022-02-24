<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\RoleTableSeeder;
use Database\Seeders\UserTableSeeder;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   *
   * @return void
   */
  public function run()
  {
    // \App\Models\User::factory(10)->create();

    $this->call([
      PermissionTableSeeder::class,
      RoleTableSeeder::class,
      UserTableSeeder::class,
      EventsTableSeeder::class,
      MealTableSeeder::class,
    ]);
  }
}
