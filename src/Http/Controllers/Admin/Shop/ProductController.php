<?php

namespace App\Http\Controllers\Admin\Shop;

use App\Models\Shop\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(Product::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Product $product)
    {
        $product = $product->query();

        if ($request->filled('filter')) {
            $product->where('title', 'like', "%{$request->filter}%");
        }

        if ($request->boolean('active')) {
            $product->onlyActive();
        }

        if ($request->boolean('deleted') ?: false) {
            $product->onlyTrashed();
        }

        $products = $product->sortBy(optional($request)->sortBy ?? 'created_at', optional($request)->direction ?? 'desc')
            ->paginate(optional($request)->rowsPerPage ?? 15);

        // Regular product results
        return new ResourceCollection($products);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Product $product)
    {
        // Set rules
        $rules = [
            'title' => 'required',
            'price' => 'required',
            'description' => 'required',
            // Images
            'media' => 'array',
            'media.*.id' => 'sometimes|required_unless:media.*.src,null|integer',
            'media.*.src' => 'required_if:media.*.id,null|string',
        ];

        // Validate those rules
        $this->validate($request, $rules);

        $product = $product->create($request->input());

        // save product's realted model
        $this->saveRelated($request, $product);

        return response()->json([
            'data' => $product->fresh(['media', 'thumbnail']),
            'message' => 'Product has been created successfully!',
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Shop\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        return response()->json($product, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Shop\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        // Set rules
        $rules = [
            'title' => 'required',
            'price' => 'required',
            'description' => 'required',
            // Images
            'media' => 'array',
            'media.*.id' => 'sometimes|required_unless:media.*.src,null|integer',
            'media.*.src' => 'required_if:media.*.id,null|string',
        ];

        // Validate those rules
        $this->validate($request, $rules);

        $product->update($request->input());

        // save product's realted model
        $this->saveRelated($request, $product);

        return response()->json([
            'data' => $product->fresh(['media', 'thumbnail']),
            'message' => 'Product has been updated successfully!',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Shop\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json([
            'message' => 'Product has been deleted successfully!',
        ], 200);
    }

    /**
     * Remove the selected resource from storage.
     *
     * @param  \App\Models\Shop\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy_selected(Request $request, Product $product)
    {
        $this->validate($request, [
            'items' => 'required',
        ]);
        $product->whereIn('id', $request->items)->each(function ($item) {
            $item->delete();
        });
        return response()->json([
            'message' => 'Products has been deleted successfully!',
        ], 200);
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param  \App\Models\Shop\Product $product
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        Product::onlyTrashed()
            ->where('id', $id)->each(function ($item) {
                $item->restore();
            });
        return response()->json([
            'message' => 'Product has been restored successfully!',
        ], 200);
    }

    /**
     * Remove the selected resource from storage.
     *
     * @param  \App\Models\Shop\Product $product
     * @return \Illuminate\Http\Response
     */
    public function restore_selected(Request $request, Product $product)
    {
        $this->validate($request, [
            'items' => 'required',
        ]);
        $product->onlyTrashed()
            ->whereIn('id', $request->items)->each(function ($item) {
                $item->restore();
            });
        return response()->json([
            'message' => 'Products has been restored successfully!',
        ], 200);
    }

    /**
     * Change active of specified resource from storage.
     *
     * @param  \App\Models\Shop\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function changeActive(Request $request, Product $product)
    {
        $product->update([
            'is_active' => !$product->is_active
        ]);

        return response()->json([
            'message' => $product->is_active ? 'Product marked as active successfully!' : 'Product marked as deactivated successfully!',
        ], 200);
    }

    /**
     * Update or Create related of selected resource from storage.
     *
     * @param  \App\Models\Shop\Product $product
     */
    protected function saveRelated(Request $request, Product $product)
    {
        // Update media
        if ($request->filled('media')) {
            $product->syncMedia($request->input('media'));
        }
    }
}
