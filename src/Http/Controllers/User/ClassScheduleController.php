<?php

namespace Coderstm\Core\Http\Controllers\User;

use Coderstm\Core\Events\BookingCreated;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Coderstm\Core\Models\ClassSchedule;
use Coderstm\Core\Http\Controllers\Controller;
use Coderstm\Core\Models\Booking;
use Coderstm\Core\Traits\Helpers;

class ClassScheduleController extends Controller
{
    use Helpers;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, ClassSchedule $classSchedule)
    {
        $startOfWeek = $request->filled('startOfWeek') ? Carbon::parse($request->startOfWeek)->startOfWeek() : now()->startOfWeek();
        $otherSortBy = $request->otherSortBy;

        $classSchedule = $classSchedule->whereBetween('date_at',  [now(), now()->addDays(6)->endOfDay()]);

        if ($request->filled('filter')) {
            $classSchedule->whereHas('class', function ($q) use ($request) {
                $q->where('name', 'like', "{$request->filter}%");
            })->orWhereHas('instructor', function ($q) use ($request) {
                $q->where('name', 'like', "{$request->filter}%");
            });
        }

        $classSchedule->orderByRaw('DATE(date_at)');

        if ($otherSortBy == 'instructor_id') {
            $classSchedule->orderByRaw('(SELECT name FROM instructors WHERE instructors.id = class_schedules.instructor_id)');
        } else if ($otherSortBy == 'class_id') {
            $classSchedule->orderByRaw('(SELECT name FROM class_lists WHERE class_lists.id = class_schedules.class_id)');
        } else {
            $classSchedule->orderBy($otherSortBy ?? 'start_at', 'asc');
        }

        $classSchedule =  $classSchedule->get()->map(function ($item) use ($request) {
            $item->is_booked = $item->isBooked(currentUser()->id);
            return $item;
        });

        $hasNext = $startOfWeek->eq(now()->startOfWeek());

        return response()->json([
            'data' => $classSchedule,
            'totalCost' => $classSchedule->sum('cost'),
            'startOfWeek' => $startOfWeek->format('Y-m-d'),
            'nextOfWeek' => $startOfWeek->addWeek()->format('Y-m-d'),
            'previousOfWeek' => $hasNext ? false : $startOfWeek->subWeeks(2)->format('Y-m-d'),
        ], 200);
    }

    /**
     * Book the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Coderstm\Core\Models\ClassSchedule  $classSchedule
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ClassSchedule $classSchedule)
    {
        if (!currentUser()->subscription()->canUseFeature('classes')) {
            abort(403, 'You have reached the maximum number of classes available for your plan. Please upgrade your plan to book more classes.');
        }

        if (!$classSchedule->bookable) {
            abort(403, 'You can only book classes 7 days ahead!');
        }

        if ($classSchedule->date_at->lt(now())) {
            abort(403, 'You can not book a class which is already started or completed!');
        }

        if ($classSchedule->has_booked) {
            abort(403, 'Slots are not available for selected class schedule! Please contact reception for standby places.');
        }

        if (currentUser()->has_blocked) {
            $release_date = currentUser()->blocked->release_at->format('d/m/Y');
            abort(403, "Sorry you are blocked from booking a class due to a No Show, the block will be lifted {$release_date}.");
        }

        $booking = $classSchedule->bookings()->updateOrCreate([
            'user_id' => currentUser()->id,
        ], [
            'canceled_at' => null,
            'source' => true,
            'is_stand_by' => false,
        ]);

        event(new BookingCreated($booking, $booking->is_stand_by));

        return response()->json([
            'message' => 'Class shedule has been booked successfully!',
        ], 200);
    }
}
