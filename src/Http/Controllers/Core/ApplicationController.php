<?php

namespace CoderstmCore\Http\Controllers\Core;

use CoderstmCore\Models\User;
use CoderstmCore\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use CoderstmCore\Http\Controllers\Controller;
use CoderstmCore\Models\Enquiry;
use CoderstmCore\Models\Message;
use CoderstmCore\Models\Task;
use CoderstmCore\Models\Issue;

class ApplicationController extends Controller
{
    /**
     * Get stats.
     *
     * @return \Illuminate\Http\Response
     */
    public function stats(Request $request)
    {
        $user = User::select('users.id', "subscriptions.created_at")->leftJoin('subscriptions', function ($join) {
            $join->on('subscriptions.user_id', '=', "users.id");
        })->whereNotNull('subscriptions.created_at');
        return response()->json([
            'total' => User::getStats('total'),
            'rolling' => User::getStats('rolling'),
            'end_date' => User::getStats('end_date'),
            'monthly' => User::getStats('month'),
            'yearly' => User::getStats('year'),
            'free' => User::getStats('free'),
            'max_year' => $user->max(DB::raw("DATE_FORMAT(subscriptions.created_at,'%Y')")),
            'min_year' => 2015,
            'unread_support' => Enquiry::onlyActive()->count(),
            'unread_tasks' => Task::onlyActive()->count(),
        ], 200);
    }

    /**
     * Get settings for a key.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSettings($key)
    {
        return response()->json(AppSetting::findByKey($key), 200);
    }

    /**
     * Update settings for a key.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateSettings(Request $request)
    {
        $rules = [
            'key' => 'required',
            'options' => 'required',
        ];

        $this->validate($request, $rules);

        AppSetting::create($request->key, $request->options);

        return response()->json([
            'message' => 'App settings has been updated successfully!'
        ], 200);
    }
}
