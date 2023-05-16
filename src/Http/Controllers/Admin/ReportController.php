<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\UserController as AdminUserController;

class ReportController extends AdminUserController
{

    public function reports(Request $request)
    {
        $rolling = User::getStatsByMonthAndYear('rolling', $request->month, $request->year);
        $end_date = User::getStatsByMonthAndYear('end_date', $request->month, $request->year);
        $cancelled = User::getStatsByMonthAndYear('cancelled', $request->month, $request->year);
        $end_date_total =  User::getStatsByMonthAndYear('end_date_total', $request->month, $request->year);
        $rolling_total = User::getStatsByMonthAndYear('rolling_total', $request->month, $request->year);
        $cancelled_total = User::getStatsByMonthAndYear('cancelled_total', $request->month, $request->year);

        return response()->json([
            'total' => User::getStatsByMonthAndYear('total', $request->month, $request->year),
            'rolling' => $rolling,
            'rolling_total' => $rolling_total,
            'end_date' => $end_date,
            'end_date_total' => $end_date_total,
            'free' => User::getStatsByMonthAndYear('free', $request->month, $request->year),
            'cancelled' => $cancelled,
            'cancelled_total' => $cancelled_total,
        ], 200);
    }

    public function reportsMonthly(Request $request)
    {
        return $this->reports($request);
    }

    public function reportsYearly(Request $request)
    {
        return $this->reports($request);
    }
}
