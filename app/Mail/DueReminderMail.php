<?php
// namespace App\Mail;

// use Illuminate\Bus\Queueable;
// use Illuminate\Contracts\Queue\ShouldQueue;
// use Illuminate\Mail\Mailable;
// use Illuminate\Queue\SerializesModels;
// use Carbon\Carbon;

// class DueReminderMail extends Mailable
// {
//     use Queueable, SerializesModels;

//     public $mailData;
    
//     public function __construct($user, $book, $dueDate)
//     {
//         $this->mailData = [
//             'name' => $user->userable->firstName,
//             'book' => $book->title,
//             'dueDate' => $this->gregorianToPersian($dueDate->format('Y-m-d')),
//         ];
//     }

//     private function gregorianToPersian($gregorianDate)
//     {
//         $dariMonths = [
//             'حمل', 'ثور', 'جوزا', 'سرطان', 'اسد', 'سنبله',
//             'میزان', 'عقرب', 'قوس', 'جدی', 'دلو', 'حوت'
//         ];

//         // Simple conversion (approximate - for exact conversion use a package)
//         list($year, $month, $day) = explode('-', $gregorianDate);
        
//         // Basic conversion (this is simplified - use a proper library for accurate conversion)
//         $persianYear = (int)$year - 621;
//         $dariMonth = $dariMonths[(int)$month - 1];
//         $persianDay = (int)$day;
        
//         return "{$persianDay} {$dariMonth} {$persianYear}";
//     }

//     public function build()
//     {
//         return $this->subject('یادآوری تاریخ برگرداندن کتاب')
//             ->view('emails.due_reminder')
//             ->with($this->mailData);
//     }
// }



namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use Morilog\Jalali\Jalalian;

class DueReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $mailData;
    
    public function __construct($user, $book, $dueDate)
    {
        $this->mailData = [
            'name'    => $user->userable->firstName,
            'book'    => $book->title,
            'dueDate' => $this->toDariJalaliDate($dueDate),
        ];
    }

    /**
     * Convert Gregorian date to Dari-formatted Jalali (Afghan) date.
     */
    private function toDariJalaliDate($date)
    {
        $jalali = Jalalian::fromCarbon(Carbon::parse($date));

        $dariMonths = [
            'حمل', 'ثور', 'جوزا', 'سرطان', 'اسد', 'سنبله',
            'میزان', 'عقرب', 'قوس', 'جدی', 'دلو', 'حوت'
        ];

        $monthIndex = (int)$jalali->format('%m') - 1;
        $day  = $jalali->format('%d');
        $year = $jalali->format('%Y');

        return "{$day} {$dariMonths[$monthIndex]} {$year}";
    }

    public function build()
    {
        return $this->subject('یادآوری تاریخ برگرداندن کتاب')
            ->view('emails.due_reminder')
            ->with($this->mailData);
    }
}
