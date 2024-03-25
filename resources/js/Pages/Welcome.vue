<script setup>
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import axios from 'axios';
import moment from 'moment';

defineProps({
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});


const refs = ref({
    subscribers: [],
    times: [],
    musicBoxes: [],
    currentSubscriber: '',
    currentMusicBox: '',
    currentZone: '',
    currentTime: '',
    currentPrayTime: '',
    activeTimer: ''
})

const loadMusicBoxes = () => {
    axios.get(route('subscriber.schedule', refs.value.currentSubscriber))
        .then(response => {
            let data = {};

            for (let counter = 0; counter < response.data.length; counter ++)
            {
                if (!data.hasOwnProperty(response.data[counter].music_box_name))
                    data[response.data[counter].music_box_name] = {};

                let schedule = {};

                for (let time in refs.value.times)
                {
                    schedule[time + '_music'] = response.data[counter][time + '_music_name'];
                    schedule[time] = response.data[counter][time + '_time'];
                }

                data[response.data[counter].music_box_name][response.data[counter].zone_name] = schedule;
            }

            refs.value.musicBoxes = data;

            loadMusicBox();
        });
};
const loadMusicBox = () => {
    let currentTime = moment().format('HH:mm:ss');
    let currentMusicBox = '', currentZone = '', currentTimeName = '', currentPrayTime = '';

    for (let musicBoxName in refs.value.musicBoxes)
    {
        for (let zoneName in refs.value.musicBoxes[musicBoxName])
        {
            for (let time in refs.value.times)
            {
                if (currentTime < refs.value.musicBoxes[musicBoxName][zoneName][time])
                {
                    if (currentPrayTime === '' || currentPrayTime > refs.value.musicBoxes[musicBoxName][zoneName][time])
                    {
                        currentMusicBox = musicBoxName;
                        currentZone = zoneName;
                        currentTimeName = time;
                        currentPrayTime = refs.value.musicBoxes[currentMusicBox][currentZone][currentTimeName];
                    }
                }
            }
        }
    }
    
    refs.value.currentMusicBox = currentMusicBox;
    refs.value.currentZone = currentZone;
    refs.value.currentTime = currentTimeName;
    refs.value.currentPrayTime = currentPrayTime ? moment(currentPrayTime, 'HH:mm:ss') : '';
};
const checkPrayingTime = () => {
    if (refs.value.currentPrayTime)
    {
        let timediff = refs.value.currentPrayTime.diff(moment());
        let countdown = moment.utc(timediff);

        if (timediff <= 0)
        {
            playMusic();
            loadMusicBox();
        }
        else
            refs.value.activeTimer = countdown;
    }
    else
        refs.value.activeTimer = '';
};
const playMusic = () => {
    let audio = new Audio('/audios/' + refs.value.musicBoxes[refs.value.currentMusicBox][refs.value.currentZone][refs.value.currentTime + '_music'] + '.mp3');
    audio.play();
};

onMounted(() => {
    axios.get(route('subscriber.list'))
        .then(response => {
            refs.value.subscribers = response.data;
            refs.value.currentSubscriber = refs.value.subscribers[0].id;

            axios.get(route('zone.list'))
                .then(response => {
                    refs.value.times = response.data;

                    loadMusicBoxes();
                });
        });
    
    setInterval(checkPrayingTime, 1000)
});
</script>

<template>
    <Head title="Welcome" />
    <div class="text-white">
        <img id="background" class="fixed h-full w-full" src="https://www.e-solat.gov.my/portalassets/images/background/imejlatar1.jpg" />
        <div class="relative min-h-screen flex flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white">
            <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
                <main class="mt-6">
                    <div class="grid gap-5 lg:grid-cols-3">
                        <div class="flex flex-none flex-col items-start overflow-hidden rounded-lg bg-opacity-50 bg-black p-10">
                            <div class="relative flex items-center lg:items-end">
                                <div id="docs-card-content" class="flex items-start gap-6 lg:flex-col">
                                    <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-white sm:size-16">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 101 101" id="user"><path d="M50.4 54.5c10.1 0 18.2-8.2 18.2-18.2S60.5 18 50.4 18s-18.2 8.2-18.2 18.2 8.1 18.3 18.2 18.3zm0-31.7c7.4 0 13.4 6 13.4 13.4s-6 13.4-13.4 13.4S37 43.7 37 36.3s6-13.5 13.4-13.5zM18.8 83h63.4c1.3 0 2.4-1.1 2.4-2.4 0-12.6-10.3-22.9-22.9-22.9H39.3c-12.6 0-22.9 10.3-22.9 22.9 0 1.3 1.1 2.4 2.4 2.4zm20.5-20.5h22.4c9.2 0 16.7 6.8 17.9 15.7H21.4c1.2-8.9 8.7-15.7 17.9-15.7z"></path></svg>
                                    </div>
                                    <div class="pt-3 sm:pt-5 lg:pt-0">
                                        <h2 class="text-3xl font-semibold">Subscribers</h2>
                                    </div>
                                    <select v-model="refs.currentSubscriber" v-on:change="loadMusicBoxes()" class="text-black">
                                        <option v-for="subscriber in refs.subscribers" :value="subscriber.id">{{ subscriber.name }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="lg:col-span-2 flex flex-none flex-col items-start overflow-hidden rounded-lg bg-opacity-50 bg-black p-10">
                            <div class="relative flex w-full items-center gap-6 lg:items-end">
                                <div id="docs-card-content" class="flex w-full items-start gap-6 lg:flex-col">
                                    <h1 v-if="refs.activeTimer === ''" class="text-center w-full">Next prayer will be available tomorrow</h1>
                                    <div v-if="refs.activeTimer !== ''" class="w-full text-center font-semibold">
                                        <h1 class="text-xl capitalize">{{ refs.times[refs.currentTime] }}</h1>
                                        <h1 class="text-lg uppercase">{{ refs.currentPrayTime.format('hh : mm a') }}</h1>
                                        <div class="flex flex-row gap-10 place-content-center">
                                            <div class="flex flex-col items-center justify-center border-8 rounded-full border-yellow-400 w-40 h-40">
                                                <h1 class="text-5xl">{{ refs.activeTimer.hours().toString().padStart(2, 0) }}</h1>
                                                <p class="text-xl">Hours</p>
                                            </div>
                                            <div class="flex flex-col items-center justify-center border-8 rounded-full border-yellow-400 w-40 h-40">
                                                <h1 class="text-5xl">{{ refs.activeTimer.minutes().toString().padStart(2, 0) }}</h1>
                                                <p class="text-xl">Minutes</p>
                                            </div>
                                            <div class="flex flex-col items-center justify-center border-8 rounded-full border-yellow-400 w-40 h-40">
                                                <h1 class="text-5xl">{{ refs.activeTimer.seconds().toString().padStart(2, 0) }}</h1>
                                                <p class="text-xl">Seconds</p>
                                            </div>
                                        </div>
                                        <p class="font-normal text-yellow-400">Until the next prayer time in the zone</p>
                                        <p class="text-lg">{{ refs.currentZone }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="lg:col-span-3 flex flex-none flex-col items-start overflow-hidden rounded-lg bg-opacity-50 bg-black p-10">
                            <div class="relative flex items-center gap-6 lg:items-end">
                                <div id="docs-card-content" class="flex items-start gap-6 lg:flex-col">
                                    <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-white sm:size-16">
                                        <svg viewBox="100 53.2781 300 400.2489" width="50" height="50" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M 199.888 53.315 L 200 153.527 L 100 153.527 L 100 253.527 L 200 253.527 L 200 453.527 L 300 453.527 L 300 253.527 L 400 253.527 L 400 153.527 L 300 153.527 C 300 153.527 300 54.087 300 53.527 C 300 52.967 200 53.527 200 53.527"/>
                                        </svg>
                                    </div>
                                    <div class="pt-3 sm:pt-5 lg:pt-0">
                                        <h2 class="text-3xl font-semibold">Subscriptions</h2>
                                        <div class="pl-4">
                                            <div v-for="(zones, musicBoxName) in refs.musicBoxes" class="mt-5">
                                                <h1 class="text-2xl">{{ musicBoxName }} Music Box</h1>
                                                <div v-for="(schedule, zoneName) in zones" class="pl-8 mt-2">
                                                    <h2 class="text-xl">{{ zoneName }}</h2>
                                                    <div class="grid gap-2 mt-2 lg:grid-cols-7">
                                                        <div v-for="(name, time) in refs.times">
                                                            <b class="font-semibold text-yellow-400 uppercase">{{ name }}</b>
                                                            <p>{{ moment(schedule[time], 'HH:mm:ss').format('hh:mm a') }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>
</template>
