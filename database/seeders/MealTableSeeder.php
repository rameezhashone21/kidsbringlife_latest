<?php

namespace Database\Seeders;

use App\Models\Meal;
use Illuminate\Database\Seeder;

class MealTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $meals = [
            [
            'title'        => 'Snack',
            'value'            => 1,
            ],
            [
            'title'        => 'Supper',
            'value'            => 2,
            ],
            [
            'title'        => 'Both Snack and Supper',
            'value'            => 3,
            ],
        ];
    
        foreach($meals as $meal) {
            Meal::create($meal);
        }
        echo "\e[32mSeeding:\e[0m Meal - complete\r\n";
    }
}
