<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SubscriberController extends Controller
{
    
    public function getSubscribers()    {
        return response()->json(Subscriber::get());
    }
    public function getSchedule($id)   {
        $query = DB::table('subscriber_music_boxes as a')->select('b.name as music_box_name', 'e.name as zone_name');

        foreach (Config::get('zone.times') as $time => $name)
            $query->addSelect("d.$time as {$time}_time", "e.$time as {$time}_music_name");

        return response()->json($query->join('music_boxes as b', 'b.id', '=', 'a.musicbox_id')
            ->join('music_box_zone_schedules as c', 'c.musicbox_id', '=', 'b.id')
            ->join('zone_schedules as d', 'd.id', '=', 'c.zone_schedule_id')
            ->join('zones as e', 'e.id', '=', 'd.zone_id')
            ->where('a.subscriber_id', $id)
            ->whereDate('d.date', date('Y-m-d'))
            ->get());
    }

}
