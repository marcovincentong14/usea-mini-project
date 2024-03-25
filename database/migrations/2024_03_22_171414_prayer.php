<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscribers', function(Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        Schema::create('music_boxes', function(Blueprint $table)   {
            $table->id();
            $table->string('name');
        });
        Schema::create('subscriber_music_boxes', function(Blueprint $table)   {
            $table->id();
            $table->foreignId('subscriber_id')->references('id')->on('subscribers');
            $table->foreignId('musicbox_id')->references('id')->on('music_boxes');
        });

        Schema::create('zones', function(Blueprint $table)   {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('imsak');
            $table->string('fajr');
            $table->string('syuruk');
            $table->string('dhuhr');
            $table->string('asr');
            $table->string('maghrib');
            $table->string('isha');
        });
        Schema::create('zone_schedules', function(Blueprint $table)    {
            $table->id();
            $table->foreignId('zone_id');
            $table->date('date');
            $table->time('imsak');
            $table->time('fajr');
            $table->time('syuruk');
            $table->time('dhuhr');
            $table->time('asr');
            $table->time('maghrib');
            $table->time('isha');
        });
        
        Schema::create('music_box_zone_schedules', function(Blueprint $table)   {
            $table->id();
            $table->foreignId('musicbox_id')->references('id')->on('music_boxes');
            $table->foreignId('zone_schedule_id')->references('id')->on('zone_schedules');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriber');
        Schema::dropIfExists('musicbox');
        Schema::dropIfExists('subscribermusicbox');
        Schema::dropIfExists('zone');
        Schema::dropIfExists('zoneprayers');
        Schema::dropIfExists('zoneschedules');
        Schema::dropIfExists('musicboxzoneschedules');
    }
};
