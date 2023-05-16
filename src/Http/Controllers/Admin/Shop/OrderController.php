<?php

namespace App\Http\Controllers\Admin\Shop;

use App\Models\Shop\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\ResourceCollection;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Order $order)
    {
        $order = $order->query();

        if ($request->filled('filter')) {
            $order->whereHas('user', function ($q) use ($request) {
                $q->whereName($request->filter);
            });
        }

        if ($request->boolean('deleted')) {
            $order->onlyTrashed();
        }

        if (is_user()) {
            $order->onlyOwner();
        }

        $order = $order->orderBy(optional($request)->sortBy ?? 'created_at', optional($request)->direction ?? 'desc')
            ->paginate(optional($request)->rowsPerPage ?? 15);
        return new ResourceCollection($order);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Order $order)
    {
        $rules = [
            'line_items' => 'required|array',
        ];

        $lineItems = collect($request->line_items);

        $request->merge([
            'status' => 'Pending',
            'user_id' => currentUser()->id,
            'total' => $lineItems->sum('price')
        ]);

        // Validate those rules
        $this->validate($request, $rules);

        // create the order
        $order = Order::create($request->input());

        $order->line_items()->sync($lineItems->mapWithKeys(function ($item) {
            return [$item['id'] => [
                'price' => $item['price'],
            ]];
        }));

        return response()->json([
            'data' => $order,
            'message' => 'Order has been created successfully!',
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Shop\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        return response()->json($order, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Shop\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {

        $rules = [
            'line_items' => 'required|array',
        ];

        // Validate those rules
        $this->validate($request, $rules);

        $lineItems = collect($request->line_items);

        $request->merge([
            'status' => 'Pending',
            'user_id' => currentUser()->id,
            'total' => $lineItems->sum('price')
        ]);

        // update the order
        $order->update($request->input());

        $order->line_items()->sync($lineItems->mapWithKeys(function ($item) {
            return [$item['id'] => [
                'price' => $item['price'],
            ]];
        }));

        return response()->json([
            'data' => $order->fresh(),
            'message' => 'Order has been update successfully!',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Shop\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        $order->delete();
        return response()->json([
            'message' => 'Order has been deleted successfully!',
        ], 200);
    }

    /**
     * Remove the selected resource from storage.
     *
     * @param  \App\Models\Shop\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy_selected(Request $request, Order $order)
    {
        $this->validate($request, [
            'items' => 'required',
        ]);
        $order->whereIn('id', $request->items)->each(function ($item) {
            $item->delete();
        });
        return response()->json([
            'message' => 'Orders has been deleted successfully!',
        ], 200);
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param  \App\Models\Shop\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        Order::onlyTrashed()
            ->where('id', $id)->each(function ($item) {
                $item->restore();
            });
        return response()->json([
            'message' => 'Order has been restored successfully!',
        ], 200);
    }

    /**
     * Remove the selected resource from storage.
     *
     * @param  \App\Models\Shop\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function restore_selected(Request $request, Order $order)
    {
        $this->validate($request, [
            'items' => 'required',
        ]);
        $order->onlyTrashed()
            ->whereIn('id', $request->items)->each(function ($item) {
                $item->restore();
            });
        return response()->json([
            'message' => 'Orders has been restored successfully!',
        ], 200);
    }
}
