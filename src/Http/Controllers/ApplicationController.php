<?php

namespace Coderstm\Http\Controllers;

use Coderstm\Models\AppSetting;
use Illuminate\Http\Request;
use Coderstm\Http\Controllers\Controller;
use Coderstm\Models\Enquiry;
use Coderstm\Models\Task;

class ApplicationController extends Controller
{
    /**
     * Get stats.
     *
     * @return \Illuminate\Http\Response
     */
    public function stats(Request $request)
    {
        return response()->json([
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
