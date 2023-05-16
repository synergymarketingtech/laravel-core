<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Registration;
use App\Http\Controllers\Controller;
use App\Traits\Helpers;
use Barryvdh\DomPDF\Facade\Pdf;

class RegistrationController extends Controller
{
    use Helpers;

    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(Registration::class, 'registration');
    }

    private function query(Request $request, Registration $registration)
    {
        $startOfWeek = $request->filled('startOfWeek') ? Carbon::parse($request->startOfWeek)->startOfWeek() : now()->startOfWeek();
        $sortBy = $request->otherSortBy != 'start_at' ? $request->otherSortBy : $request->sortBy;
        $otherSortBy = $request->otherSortBy != 'start_at' ? $request->sortBy : $request->otherSortBy;

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $from = Carbon::parse($request->input('date_from'));
            $to = Carbon::parse($request->input('date_to'));
            $date_from = $from->format('Y-m-d');
            $date_to = $to->format('Y-m-d');
            $registration = $registration->whereRaw('date_at BETWEEN ? AND ?',  [$date_from, $date_to]);
            $date = $from->format('d/m/Y') . ' - ' . $to->format('d/m/Y');
        } else if ($request->filled('date')) {
            $date = Carbon::parse($request->input('date'));
            $registration = $registration->where([
                'day' => $date->dayName,
                'start_of_week' => $date->startOfWeek()
            ]);
            $date = $date->format('d/m/Y');
        } else {
            $registration = $registration->where('start_of_week', $startOfWeek);
            $date = $startOfWeek->format('d/m/Y');
        }

        if ($request->filled('filter')) {
            $registration->whereHas('class', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->filter}%");
            });
        }

        if ($request->filled('class')) {
            $registration->where('class_schedules.class_id', $request->class);
        }

        if ($request->filled('location')) {
            $registration->where('location_id', $request->location);
        }

        if ($request->filled('instructor')) {
            $registration->where('class_schedules.instructor_id', $request->instructor);
        }

        if ($request->boolean('deleted')) {
            $registration->onlyTrashed();
        }


        $registrations =  $registration->orderBy($sortBy ?? 'created_at', optional($request)->direction ?? 'desc');

        if ($otherSortBy == 'instructor_id') {
            $registrations->orderByRaw('(SELECT name FROM instructors WHERE instructors.id = class_schedules.instructor_id)');
        } else if ($otherSortBy == 'class_id') {
            $registrations->orderByRaw('(SELECT name FROM class_lists WHERE class_lists.id = class_schedules.class_id)');
        } else {
            $registrations->orderBy($otherSortBy ?? 'start_at', 'asc');
        }

        return $registrations;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Registration $registration)
    {
        $startOfWeek = $request->filled('startOfWeek') ? Carbon::parse($request->startOfWeek)->startOfWeek() : now()->startOfWeek();

        $registrations = $this->query($request, $registration);

        $totalCost = $registrations->sum('cost');

        $registrations = $registrations->paginate(optional($request)->rowsPerPage ?? 15);

        return response()->json([
            'data' => $registrations->items(),
            'meta' => [
                'total' => $registrations->total(),
                'per_page' => $registrations->perPage(),
                'current_page' => $registrations->currentPage(),
                'last_page' => $registrations->lastPage(),
            ],
            'totalCost' => $totalCost,
            'startOfWeek' => $startOfWeek->format('Y-m-d'),
            'nextOfWeek' => $startOfWeek->addDays(7)->startOfWeek()->format('Y-m-d'),
            'previousOfWeek' => $startOfWeek->subDays(8)->startOfWeek()->format('Y-m-d'),
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Registration  $registration
     * @return \Illuminate\Http\Response
     */
    public function show(Registration $registration)
    {
        return response()->json($registration->load(['active_bookings', 'stand_by_bookings']), 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Registration  $registration
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Registration $registration)
    {
        if ($request->boolean('has_sign_off')) {
            $request->merge([
                'sign_off_at' => now(),
                'admin_id' => currentUser()->id
            ]);
        } else {
            $request->merge([
                'sign_off_at' => null,
                'admin_id' => null
            ]);
        }
        // update the registration
        $registration->update($request->input());

        if ($request->filled('active_bookings')) {
            $registration->syncActiveBookings(collect($request->active_bookings));
        }

        if ($request->filled('stand_by_bookings')) {
            $registration->syncStandbyBookings(collect($request->stand_by_bookings));
        }

        return response()->json([
            'data' => $registration->fresh(['active_bookings', 'stand_by_bookings']),
            'message' => 'Class schedule has been update successfully!',
        ], 200);
    }

    /**
     * Change sign_off of specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Registration  $registration
     * @return \Illuminate\Http\Response
     */
    public function changeSignOff(Request $request, Registration $registration)
    {
        $registration->update([
            'sign_off_at' => $registration->has_sign_off ? null : now()
        ]);

        return response()->json([
            'message' => !$registration->has_sign_off ? 'Registration marked as sign off successfully!' : 'Registration unmarked as sign off successfully!',
        ], 200);
    }

    /**
     * Print the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Registration  $registration
     * @return \Illuminate\Http\Response
     */
    public function pdf(Request $request, Registration $registration)
    {
        return Pdf::loadView('pdfs.registration', [
            'registration' => $registration
        ])->download("registration-{$registration->label}.pdf");
    }

    /**
     * Print a listing pdf of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function listPdf(Request $request, Registration $registration)
    {
        $startOfWeek = $request->filled('startOfWeek') ? Carbon::parse($request->startOfWeek)->startOfWeek() : now()->startOfWeek();

        $registrations = $this->query($request, $registration);

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $from = Carbon::parse($request->input('date_from'));
            $to = Carbon::parse($request->input('date_to'));
            $date = $from->format('d/m/Y') . ' - ' . $to->format('d/m/Y');
        } else if ($request->filled('date')) {
            $date = Carbon::parse($request->input('date'));
            $date = $date->format('d/m/Y');
        } else {
            $date = $startOfWeek->format('d/m/Y');
        }

        $totalCost = $registrations->sum('cost');
        $registrations = $registrations->get();

        if (in_array($request->otherSortBy, ["class_id", "instructor_id"])) {
            $registrations = $registrations->groupBy(function ($item) use ($request) {
                return $item[$request->otherSortBy];
            })->map(function ($items) {
                $items->push([
                    'is_total' => true,
                    'cost' => $items->sum('cost')
                ]);
                return $items->values();
            });
            $registrations = $registrations->flatten(1)->values();
        }

        $time = now()->timestamp;
        return Pdf::loadView('pdfs.registrations', [
            'date' => $date,
            'total' => $totalCost,
            'registrations' => $registrations
        ])->setPaper('A4', 'landscape')->stream("registrations-{$time}.pdf");
    }
}
