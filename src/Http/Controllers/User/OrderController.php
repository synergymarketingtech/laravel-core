<?php

namespace CoderstmCore\Http\Controllers\User;

use CoderstmCore\Models\Shop\Order;
use Illuminate\Http\Request;
use CoderstmCore\Http\Controllers\Controller;
use CoderstmCore\Models\Enquiry;
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
        $order = $order->onlyOwner();

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
            'total' => $lineItems->map(function ($item) {
                return $item['price'] * $item['quantity'];
            })->sum()
        ]);

        // Validate those rules
        $this->validate($request, $rules);

        // create the order
        $order = Order::create($request->input());

        $order->line_items()->sync($lineItems->mapWithKeys(function ($item) {
            return [$item['id'] => [
                'price' => $item['price'],
                'size' => $item['size'],
                'quantity' => $item['quantity'],
            ]];
        }));

        Enquiry::create([
            'subject' => "Order #{$order->id} Enquiry",
            'message' => $request->note,
            'order_id' => $order->id,
        ]);

        return response()->json([
            'data' => $order->fresh(['line_items']),
            'message' => 'Order has been created successfully!',
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \CoderstmCore\Models\Shop\Order  $order
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
     * @param  \CoderstmCore\Models\Shop\Order  $order
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
                'size' => $item['size'],
                'quantity' => $item['quantity'],
            ]];
        }));

        return response()->json([
            'data' => $order->fresh(['line_items']),
            'message' => 'Order has been update successfully!',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \CoderstmCore\Models\Shop\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        $order->forceDelete();
        return response()->json([
            'message' => 'Order has been deleted successfully!',
        ], 200);
    }
}
