<?php

namespace App\Http\Controllers\Api\BackControllers;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Duration;
use App\Models\Reserve;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{


    public function getBookReport(Request $request)
    {
        $category   = $request->input('category', 'all');
        $department = $request->input('department', 'all');
        $format     = $request->input('format', 'all');
        $code       = $request->input('code', 'all');
        $borrow     = $request->input('borrow', 'all');

        $query = Book::with(['category', 'department', 'image'])
            ->select('id', 'title', 'author', 'format', 'code', 'cat_id', 'dep_id', 'borrow', 'created_at');

        if ($category !== 'all') {
            $query->where('cat_id', $category);
        }

        if ($department !== 'all') {
            $query->where('dep_id', $department);
        }

        if ($format !== 'all') {
            $query->where('format', $format);
        }

        if ($code !== 'all') {
            $query->where('code', $code);
        }

        if ($borrow !== 'all') {
            $query->where('borrow', $borrow);
        }

        $books = $query->get()->map(function ($book) {
            return [
                'title'        => $book->title,
                'author'       => $book->author,
                'format'       => $book->format,
                'code'         => $book->code,
                'borrow'       => $book->borrow,
                'created_at'   => $book->created_at->format('Y-m-d'),
                'image_url' => $book->image ? asset($book->image->image) : null,
                'department'   => optional($book->department)->name,
                'category'     => optional($book->category)->name,
            ];
        });

        return response()->json([
            'data' => $books
        ]);
    }

    public function getStudents(Request $request)
    {
        $facId   = $request->input('fac_id', 'all');
        $depId   = $request->input('dep_id', 'all');
        $deleted = $request->input('deleted', 'no');
        $status  = $request->input('status', null);

        $query = User::with(['userable.faculty', 'userable.department'])
            ->where('type', 'student');

        if ($deleted === 'yes') {
            $query->onlyTrashed();
        } else {
            $query->whereNull('deleted_at');

            if (in_array($status, ['active', 'inactive'])) {
                $query->where('status', $status);
            }
        }

        $query->whereHasMorph('userable', 'App\Models\Student', function ($q) use ($facId, $depId) {
            if ($facId !== 'all') {
                $q->where('fac_id', $facId);
            }
            if ($depId !== 'all') {
                $q->where('dep_id', $depId);
            }
        });

        $students = $query->get()->map(function ($user) {
            $student = $user->userable;

            return [
                'id'         => $user->id,
                'user_id'    => $user->user_id,
                'username'   => $student->firstName . ' ' . $student->lastName,
                'email'      => $user->email,
                'faculty'    => optional($student->faculty)->name,
                'department' => optional($student->department)->name,
            ];
        });

        return response()->json([
            'data' => $students
        ]);
    }




    public function getReserve(Request $request)
    {
        $dep_id = $request->input('dep_id', 'all');

        $durations = Duration::where('time', 'notFinished')
            ->whereHas('reserve', function ($query) use ($dep_id) {
                $query->where('status', 'active')
                    ->whereNull('deleted_at')
                    ->whereHas('user', function ($userQuery) use ($dep_id) {
                        $userQuery->where('type', 'student')
                            ->whereHasMorph('userable', [\App\Models\Student::class], function ($query) use ($dep_id) {
                                if ($dep_id !== 'all') {
                                    $query->where('dep_id', $dep_id);
                                }
                            });
                    });
            })
            ->with([
                'reserve.book:id,title,author',
                'reserve.user:id,user_id,email,userable_id,userable_type',
                'reserve.user.userable:id,firstName,lastName'
            ])
            ->get()
            ->map(function ($duration) {
                $reserve = $duration->reserve;
                $user = $reserve->user;
                $userable = $user->userable;

                return [
                    'title'       => $reserve->book->title ?? null,
                    'author'      => $reserve->book->author ?? null,
                    'username'    => $userable->firstName . ' ' . $userable->lastName,
                    'email'       => $user->email,
                    'user_id'     => $user->user_id,
                    'borrowed_at' => $duration->borrowed_at,
                    'return_by'   => $duration->return_by,
                ];
            });

        return response()->json([
            'data' => $durations
        ]);
    }
}
