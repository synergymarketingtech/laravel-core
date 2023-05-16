<?php

namespace App\Http\Controllers\User;

use App\Models\Shop\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ShopController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function products(Request $request, Product $product)
    {
        $product = $product->query();

        if ($request->filled('filter')) {
            $product->where('title', 'like', "%{$request->filter}%");
        }

        $product->onlyActive();

        $products = $product->sortBy(optional($request)->sortBy ?? 'created_at', optional($request)->direction ?? 'desc')
            ->paginate(optional($request)->rowsPerPage ?? 15);

        // Regular product results
        return new ResourceCollection($products);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function product($slug)
    {
        $product = Product::whereSlug($slug)->first();
        if (!$product) {
            abort(404, 'Product not found!');
        }
        return response()->json($product, 200);
    }
}
