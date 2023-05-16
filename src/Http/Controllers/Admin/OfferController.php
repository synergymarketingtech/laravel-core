<?php

namespace Coderstm\Core\Http\Controllers\Admin;

use Coderstm\Core\Models\Offer;
use Coderstm\Core\Models\Core\File;
use Illuminate\Http\Request;
use Coderstm\Core\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\ResourceCollection;

class OfferController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(Offer::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Offer $offer)
    {
        $offer = $offer->query();

        if ($request->filled('filter')) {
            $offer->where('title_line_1', 'like', "%{$request->filter}%");
        }

        if ($request->boolean('active')) {
            $offer->onlyActive();
        }

        if ($request->boolean('deleted')) {
            $offer->onlyTrashed();
        }

        $offer = $offer->orderBy(optional($request)->sortBy ?? 'created_at', optional($request)->direction ?? 'desc')
            ->paginate(optional($request)->rowsPerPage ?? 15);
        return new ResourceCollection($offer);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Offer $offer)
    {
        $rules = [
            'title_line_1' => 'required',
        ];

        // Validate those rules
        $this->validate($request, $rules);

        // create the offer
        $offer = Offer::create($request->input());

        if ($request->filled('thumbnail')) {
            $offer->media()->sync(File::find($request->thumbnail['id']));
        }

        return response()->json([
            'data' => $offer->fresh(['thumbnail']),
            'message' => 'Offer has been created successfully!',
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Coderstm\Core\Models\Offer  $offer
     * @return \Illuminate\Http\Response
     */
    public function show(Offer $offer)
    {
        return response()->json($offer, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Coderstm\Core\Models\Offer  $offer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Offer $offer)
    {

        $rules = [
            'title_line_1' => 'required',
        ];

        // Validate those rules
        $this->validate($request, $rules);

        // update the offer
        $offer->update($request->input());

        if ($request->filled('thumbnail')) {
            $offer->media()->sync(File::find($request->thumbnail['id']));
        }

        return response()->json([
            'data' => $offer->fresh(['thumbnail']),
            'message' => 'Offer has been update successfully!',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Coderstm\Core\Models\Offer  $offer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Offer $offer)
    {
        $offer->delete();
        return response()->json([
            'message' => 'Offer has been deleted successfully!',
        ], 200);
    }

    /**
     * Remove the selected resource from storage.
     *
     * @param  \Coderstm\Core\Models\Offer  $offer
     * @return \Illuminate\Http\Response
     */
    public function destroy_selected(Request $request, Offer $offer)
    {
        $this->validate($request, [
            'items' => 'required',
        ]);
        $offer->whereIn('id', $request->items)->each(function ($item) {
            $item->delete();
        });
        return response()->json([
            'message' => 'Offers has been deleted successfully!',
        ], 200);
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param  \Coderstm\Core\Models\Offer  $offer
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        Offer::onlyTrashed()
            ->where('id', $id)->each(function ($item) {
                $item->restore();
            });
        return response()->json([
            'message' => 'Offer has been restored successfully!',
        ], 200);
    }

    /**
     * Remove the selected resource from storage.
     *
     * @param  \Coderstm\Core\Models\Offer  $offer
     * @return \Illuminate\Http\Response
     */
    public function restore_selected(Request $request, Offer $offer)
    {
        $this->validate($request, [
            'items' => 'required',
        ]);
        $offer->onlyTrashed()
            ->whereIn('id', $request->items)->each(function ($item) {
                $item->restore();
            });
        return response()->json([
            'message' => 'Offers has been restored successfully!',
        ], 200);
    }

    /**
     * Change active of specified resource from storage.
     *
     * @param  \Coderstm\Core\Models\Offer  $offer
     * @return \Illuminate\Http\Response
     */
    public function changeActive(Request $request, Offer $offer)
    {
        $offer->update([
            'is_active' => !$offer->is_active
        ]);

        return response()->json([
            'message' => $offer->is_active ? 'Offer marked as active successfully!' : 'Offer marked as deactivated successfully!',
        ], 200);
    }
}
