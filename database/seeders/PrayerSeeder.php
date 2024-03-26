<?php

namespace Database\Seeders;

use App\Models\MusicBox;
use App\Models\Subscriber;
use App\Models\Zone;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PrayerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "Generating subscribers\n";
        Subscriber::factory(10)->create();
        echo "Generating music boxes\n";
        MusicBox::factory(10)->create();

        echo "Assigning music boxes to subscribers\n";
        $subscribers = Subscriber::pluck('id');
        $musicboxes = MusicBox::pluck('id');

        // assign subscribers to 1 or more music boxes
        foreach ($subscribers as $subscriber)
        {
            $subscribermusicboxes = [];

            for ($counter = 0; $counter < rand(1, 10); $counter ++)
            {
                do
                    $musicbox = fake()->randomElement($musicboxes);
                while (in_array($musicbox, $subscribermusicboxes));

                DB::table('subscriber_music_boxes')->insert([
                    'subscriber_id' => $subscriber,
                    'musicbox_id' => $musicbox
                ]);

                $subscribermusicboxes[] = $musicbox;
            }
            echo "\t" . count($subscribermusicboxes) . " assigned to subscriber #$subscriber \n";
        }

        echo "Generating zone schedules\n";
        // create zones
        $times = Config::get('zone.times');

        foreach (Config::get('zone.list') as $zones)
            foreach ($zones as $code => $name)
            {
                $zone = new Zone();
                $zone->code = $code;
                $zone->name = $name;

                foreach ($times as $time => $name)
                    $zone->$time = rand(1, 20);

                $zone->save();

                echo "\tGetting week schedule for $code\n";
                // get zone schedule for the week
                $weekSchedule = Http::get('https://www.e-solat.gov.my', [
                    'r' => 'esolatApi/takwimsolat',
                    'period' => 'week',
                    'zone' => $code
                ])->collect('prayerTime');

                foreach ($weekSchedule as $schedule)
                {
                    $data = [
                        'zone_id' => $zone->id,
                        'date' => date('Y-m-d', strtotime($schedule['date']))
                    ];

                    foreach ($times as $time => $name)
                        $data[$time] = $schedule[$time];

                    DB::table('zone_schedules')->insert($data);
                }
            }

        echo "Assigning zone prayers to music boxes\n";
        // assign 1 or more zone schedules to music boxes
        $schedules = DB::table('zone_schedules')->pluck('id');

        foreach ($musicboxes as $musicbox)
        {
            $musicboxzoneschedules = [];

            for ($counter = 0; $counter < rand(1, count($schedules)); $counter ++)
            {
                do
                    $zoneschedule = fake()->randomElement($schedules);
                while (in_array($zoneschedule, $musicboxzoneschedules));

                DB::table('music_box_zone_schedules')->insert([
                    'musicbox_id' => $musicbox,
                    'zone_schedule_id' => $zoneschedule
                ]);

                $musicboxzoneschedules[] = $zoneschedule;
            }

            echo "\t" . count($musicboxzoneschedules) . " assigned to music box #$musicbox \n";
        }
    }

}