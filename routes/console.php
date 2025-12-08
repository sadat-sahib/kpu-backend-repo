<?php

use App\Models\Duration;
use App\Models\Fine;
use App\Models\Role;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Book;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::call(function () {

    $durations = Duration::where('time', '=', 'notFinished')->get();

    foreach ($durations as $duration) {
        $finishedTime = Carbon::parse($duration->return_by);
        if ($finishedTime->isSameDay(Carbon::now())) {
            $duration->time = "finished";
            $duration->save();
            Fine::create([
                'user_id' => $duration->reserve->user->id,
                'book_id' => $duration->reserve->book->id,
                'amount' => 500,
                'issue_date' => Carbon::now()->format('Y-m-d'),
                'paid' => 'no'
            ]);
        }
    }




})->everyTenSeconds();

// 🔔 Email reminders (every 5 minutes for testing)
// Schedule::command('app:send-due-reminders')->everyFiveMinutes();

// Schedule::command('app:send-due-reminders')->dailyAt('08:00');

// Testing mode
Schedule::command('app:send-due-reminders')->everyMinute();
