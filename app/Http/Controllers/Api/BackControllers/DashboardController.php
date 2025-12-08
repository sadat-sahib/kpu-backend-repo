<?php

namespace App\Http\Controllers\Api\BackControllers;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Faculty;
use App\Models\Reserve;
use App\Models\User;

use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{

    public function index()
    {
        $faculties = Faculty::select('id', 'name')
            ->get()
            ->toArray();

        return response()->json(['faculties' => $faculties]);
    }

    public function stats(): JsonResponse
    {
        // ===== Student Stats =====
        $activeStudents = User::where('type', 'student')
            ->where('status', 'active')
            ->count();
        $deactiveStudents = User::where('type', 'student')
            ->where('status', 'inactive')
            ->count();

        $deletedStudents = User::onlyTrashed()
            ->where('type', 'student')
            ->count();

        // ===== Teacher Stats =====
        $activeTeachers = User::where('type', 'teacher')
            ->where('status', 'active')
            ->count();

        $deletedTeachers = User::onlyTrashed()
            ->where('type', 'teacher')
            ->count();

        // ===== Book Stats =====
        $totalBooks = Book::count();
        $pdfBooks = Book::where('format', 'pdf')->count();
        $hardBooks = Book::where('format', 'hard')->count();
        $bothBooks = Book::where('format', 'both')->count();

        // ===== Reservation Stats =====
        $pendingReserves = Reserve::where('status', 'inactive')->count(); // waiting for approval
        $activeReserves = Reserve::where('status', 'active')->count();   // accepted / borrowed
        $totalRequests = Reserve::count();                               // all-time requests

        return response()->json([
            'user_stats' => [
                'active_students' => $activeStudents,
                'deactive_students' => $deactiveStudents,
                'deleted_students' => $deletedStudents,
                'active_teachers' => $activeTeachers,
                'deleted_teachers' => $deletedTeachers,
            ],
            'book_stats' => [
                'total_books' => $totalBooks,
                'pdf_books' => $pdfBooks,
                'hard_books' => $hardBooks,
                'both_format_books' => $bothBooks,
            ],
            'reservation_stats' => [
                'pending_reserves' => $pendingReserves,
                'active_reserves' => $activeReserves,
                'total_requests' => $totalRequests,
            ],
        ]);
    }

    public function getFacultyWithDepartments()
    {

        $faculties = Faculty::with('departments:id,name,fac_id')
            ->get(['id', 'name'])
            ->map(function ($f) {
                return [
                    'id'          => $f->id,
                    'name'        => $f->name,
                    'departments' => $f->departments
                        ->map(fn($d) => [
                            'id'   => $d->id,
                            'name' => $d->name,
                        ])
                        ->toArray(),
                ];
            });

        return response()->json(['data' => $faculties]);
    }
}
