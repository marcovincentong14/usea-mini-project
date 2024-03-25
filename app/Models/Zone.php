<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [ 'code', 'name', 'imsak', 'fajr', 'syuruk', 'dhuhr', 'asr', 'maghrib', 'isha' ];

}
