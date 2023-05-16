<?php

namespace Coderstm\Core\Http\Controllers\Admin;

use Coderstm\Core\Models\Location;
use Illuminate\Http\Request;
use Coderstm\Core\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\ResourceCollection;

class LocationController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(Location::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Location $location)
    {
        $location = $location->query();

        if ($request->filled('filter')) {
            $location->where('label', 'like', "%{$request->filter}%");
        }

        if ($request->boolean('active')) {
            $location->onlyActive();
        }

        if ($request->boolean('deleted')) {
            $location->onlyTrashed();
        }

        $location = $location->orderBy(optional($request)->sortBy ?? 'created_at', optional($request)->direction ?? 'desc')
            ->paginate(optional($request)->rowsPerPage ?? 15);
        return new ResourceCollection($location);
    }

    /**
     * Display a options listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function options(Request $request, Location $location)
    {
        $request->merge([
            'option' => true
        ]);
        return $this->index($request, $location);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Location $location)
    {
        $rules = [
            'label' => 'required',
        ];

        // Validate those rules
        $this->validate($request, $rules);

        // create the location
        $location = Location::create($request->input());

        return response()->json([
            'data' => $location,
            'message' => 'Location has been created successfully!',
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Coderstm\Core\Models\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function show(Location $location)
    {
        return response()->json($location, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Coderstm\Core\Models\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Location $location)
    {

        $rules = [
            'label' => 'required',
        ];

        // Validate those rules
        $this->validate($request, $rules);

        // update the location
        $location->update($request->input());

        return response()->json([
            'data' => $location->fresh(),
            'message' => 'Location has been update successfully!',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Coderstm\Core\Models\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function destroy(Location $location)
    {
        $location->delete();
        return response()->json([
            'message' => 'Location has been deleted successfully!',
        ], 200);
    }

    /**
     * Remove the selected resource from storage.
     *
     * @param  \Coderstm\Core\Models\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function destroy_selected(Request $request, Location $location)
    {
        $this->validate($request, [
            'items' => 'required',
        ]);
        $location->whereIn('id', $request->items)->each(function ($item) {
            $item->delete();
        });
        return response()->json([
            'message' => 'Locations has been deleted successfully!',
        ], 200);
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param  \Coderstm\Core\Models\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        Location::onlyTrashed()
            ->where('id', $id)->each(function ($item) {
                $item->restore();
            });
        return response()->json([
            'message' => 'Location has been restored successfully!',
        ], 200);
    }

    /**
     * Remove the selected resource from storage.
     *
     * @param  \Coderstm\Core\Models\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function restore_selected(Request $request, Location $location)
    {
        $this->validate($request, [
            'items' => 'required',
        ]);
        $location->onlyTrashed()
            ->whereIn('id', $request->items)->each(function ($item) {
                $item->restore();
            });
        return response()->json([
            'message' => 'Locations has been restored successfully!',
        ], 200);
    }

    /**
     * Change active of specified resource from storage.
     *
     * @param  \Coderstm\Core\Models\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function changeActive(Request $request, Location $location)
    {
        $location->update([
            'is_active' => !$location->is_active
        ]);

        return response()->json([
            'message' => $location->is_active ? 'Location marked as active successfully!' : 'Location marked as deactivated successfully!',
        ], 200);
    }
}
