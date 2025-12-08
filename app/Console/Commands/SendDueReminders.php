<?php

// namespace App\Console\Commands;

// use Illuminate\Console\Command;
// use App\Mail\DueReminderMail;
// use App\Models\Reserve;
// use Carbon\Carbon;
// use Illuminate\Support\Facades\Mail;

// class SendDueReminders extends Command
// {
//     protected $signature = 'app:send-due-reminders';
//     protected $description = 'ارسال ایمیل یادآوری برای کتاب هایی که فردا باید برگردانده شوند';

//     public function handle()
//     {
//         $tomorrow = now()->addDay()->toDateString();
        
//         $reserves = Reserve::with(['user.userable', 'book'])
//             ->where('status', 'active')
//             ->whereDate('due_at', $tomorrow)
//             ->get();

//         foreach ($reserves as $reserve) {
//             Mail::to($reserve->user->email)->queue(
//                 new DueReminderMail(
//                     $reserve->user,
//                     $reserve->book,
//                     Carbon::parse($reserve->due_at)
//                 )
//             );
//         }

//         $this->info("یادآوری برای {$reserves->count()} کتاب ارسال شد.");
//     }
// }


namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Mail\DueReminderMail;
use App\Models\Reserve;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendDueReminders extends Command
{
    protected $signature = 'app:send-due-reminders';
    protected $description = 'ارسال ایمیل یادآوری برای کتاب‌هایی که فردا باید برگردانده شوند';

    public function handle()
    {
        $tomorrow = now()->addDay()->toDateString();

        $reserves = Reserve::with(['user.userable', 'book'])
            ->where('status', 'active')
            ->whereDate('due_at', $tomorrow)
            ->get();

        foreach ($reserves as $reserve) {
            Mail::to($reserve->user->email)->queue(
                new DueReminderMail(
                    $reserve->user,
                    $reserve->book,
                    Carbon::parse($reserve->due_at)
                )
            );
        }

        $this->info("یادآوری برای {$reserves->count()} کتاب ارسال شد.");
    }
}
