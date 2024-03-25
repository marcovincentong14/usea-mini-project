<?php

use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\ZoneController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
});

Route::get('/subscribers', [ SubscriberController::class, 'getSubscribers' ])->name('subscriber.list');
Route::get('/subscribers/{id}/schedule', [ SubscriberController::class, 'getSchedule' ])->name('subscriber.schedule');

Route::get('/zones', [ ZoneController::class, 'getZones' ])->name('zone.list');
