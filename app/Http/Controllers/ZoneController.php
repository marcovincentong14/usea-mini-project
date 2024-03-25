<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class ZoneController extends Controller
{
    
    public function getZones()  {
        return response()->json(Config::get('zone.times'));
    }

}
