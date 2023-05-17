<?php

namespace CoderstmCore\Http\Controllers\Core;

use CoderstmCore\Http\Controllers\Controller;
use CoderstmCore\Models\Log;
use Illuminate\Http\Request;

class LogController extends Controller
{

    /**
     * Display the specified resource.
     *
     * @param  \CoderstmCore\Models\Log $log
     * @return \Illuminate\Http\Response
     */
    public function show(Log $log)
    {
        return response()->json($log->load([
            'user',
            'media',
        ]), 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \CoderstmCore\Models\Log $log
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Log $log)
    {
        // Set rules
        $rules = [
            'message' => 'required',
        ];

        // Validate those rules
        $this->validate($request, $rules);

        $log->update($request->input());

        // Update media
        if ($request->has('media')) {
            $log->setMedia($request->input('media'));
        }

        return response()->json([
            'data' => $log->fresh()->load([
                'admin',
                'media',
            ]),
            'message' => 'Log has been updated successfully!',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \CoderstmCore\Models\Log $log
     * @return \Illuminate\Http\Response
     */
    public function destroy(Log $log)
    {
        $log->delete();
        return response()->json([
            'message' => 'Log has been deleted successfully!',
        ], 200);
    }

    /**
     * Store a reply to specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \CoderstmCore\Models\Log $log
     * @return \Illuminate\Http\Response
     */
    public function reply(Request $request, Log $log)
    {
        $rules = [
            'message' => 'required',
        ];

        $this->validate($request, $rules);

        $log = $log->reply()->create($request->input());

        // Update media
        if ($request->has('media')) {
            $log->setMedia($request->input('media'));
        }

        return response()->json([
            'data' => $log->load([
                'admin',
                'media',
            ]),
            'message' => 'Log reply has been created successfully!',
        ], 200);
    }
}
