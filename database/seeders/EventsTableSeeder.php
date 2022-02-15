<?php

namespace Database\Seeders;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class eventsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /**
         * Events
         *
         */
        $events = [
            [
            'event_name'        => 'event1',
            'details'           => 'event1',
            'start_date'        => Carbon::now(),
            'end_date'          => Carbon::now()->addDay(2),
            'meal_type'         => 'snaks',
            'status'            => 1,
            ],
            [
            'event_name'        => 'event2',
            'details'           => 'event2',
            'start_date'        => Carbon::now(),
            'end_date'          => Carbon::now()->addDay(2),
            'meal_type'         => 'snaks',
            'status'            => 0,
            ],
            [
            'event_name'        => 'event3',
            'details'           => 'event3',
            'start_date'        => Carbon::now(),
            'end_date'          => Carbon::now()->addDay(2),
            'meal_type'         => 'snaks',
            'status'            => 1,
            ],
        ];
    
        foreach($events as $event) {
            Event::create($event);
        }
        echo "\e[32mSeeding:\e[0m Event - complete\r\n";
    }
}
