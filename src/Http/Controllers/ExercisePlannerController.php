<?php

namespace Coderstm\Core\Http\Controllers;

use Illuminate\Http\Request;
use Coderstm\Core\Models\ExercisePlanner;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ExercisePlannerController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, ExercisePlanner $exercisePlanner)
    {
        $exercisePlanner = $exercisePlanner->query();

        if ($request->filled('filter')) {
            $exercisePlanner->where('name', 'like', "%{$request->filter}%");
        }

        if ($request->boolean('active')) {
            $exercisePlanner->onlyActive();
        }

        if ($request->boolean('deleted')) {
            $exercisePlanner->onlyTrashed();
        }

        $exercisePlanner = $exercisePlanner->orderBy(optional($request)->sortBy ?? 'created_at', optional($request)->direction ?? 'desc')
            ->paginate(optional($request)->rowsPerPage ?? 15);
        return new ResourceCollection($exercisePlanner);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, ExercisePlanner $exercisePlanner)
    {
        $rules = [
            'name' => 'required',
            'description' => 'required',
        ];

        // Validate those rules
        $this->validate($request, $rules);

        // create the exercisePlanner
        $exercisePlanner = ExercisePlanner::create($request->input());

        return response()->json([
            'data' => $exercisePlanner,
            'message' => 'Exercise planner has been created successfully!',
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Coderstm\Core\Models\ExercisePlanner  $exercisePlanner
     * @return \Illuminate\Http\Response
     */
    public function show(ExercisePlanner $exercisePlanner)
    {
        return response()->json($exercisePlanner, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Coderstm\Core\Models\ExercisePlanner  $exercisePlanner
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ExercisePlanner $exercisePlanner)
    {

        $rules = [
            'name' => 'required',
            'description' => 'required',
        ];

        // Validate those rules
        $this->validate($request, $rules);

        // update the exercisePlanner
        $exercisePlanner->update($request->input());

        return response()->json([
            'data' => $exercisePlanner->fresh(),
            'message' => 'Exercise planner has been update successfully!',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Coderstm\Core\Models\ExercisePlanner  $exercisePlanner
     * @return \Illuminate\Http\Response
     */
    public function destroy(ExercisePlanner $exercisePlanner)
    {
        $exercisePlanner->forceDelete();
        return response()->json([
            'message' => 'Exercise planner has been deleted successfully!',
        ], 200);
    }

    /**
     * Change active of specified resource from storage.
     *
     * @param  \Coderstm\Core\Models\ExercisePlanner  $exercisePlanner
     * @return \Illuminate\Http\Response
     */
    public function changeActive(Request $request, ExercisePlanner $exercisePlanner)
    {
        $exercisePlanner->update([
            'is_active' => !$exercisePlanner->is_active
        ]);

        return response()->json([
            'message' => $exercisePlanner->is_active ? 'Exercise planner marked as active successfully!' : 'Exercise planner marked as deactivated successfully!',
        ], 200);
    }
}
