<?php

namespace CoderstmCore\Http\Controllers;

use CoderstmCore\Models\Referral;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ReferralController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Referral $referral)
    {
        $referral = $referral->query();

        if ($request->filled('filter')) {
            $referral->whereName($request->filter);
            $referral->orWhere('email', 'like', "%{$request->filter}%");
        }

        if ($request->boolean('deleted')) {
            $referral->onlyTrashed();
        }

        if (is_user()) {
            $referral->onlyOwner();
        }

        $referral = $referral->sortBy(optional($request)->sortBy ?? 'created_at', optional($request)->direction ?? 'desc')
            ->paginate(optional($request)->rowsPerPage ?? 15);
        return new ResourceCollection($referral);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Referral $referral)
    {
        $rules = [
            'title' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:referrals',
            'phone_number' => 'required',
        ];

        // Validate those rules
        $this->validate($request, $rules);

        // create the Referral
        $referral = Referral::create($request->input());

        return response()->json([
            'data' => $referral,
            'message' => 'Referral has been created successfully!',
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \CoderstmCore\Models\Referral  $referral
     * @return \Illuminate\Http\Response
     */
    public function show(Referral $referral)
    {
        return response()->json($referral, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \CoderstmCore\Models\Referral  $referral
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Referral $referral)
    {

        $rules = [
            'title' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => "email|unique:referrals,email,{$referral->id}",
            'phone_number' => 'required',
        ];

        // Validate those rules
        $this->validate($request, $rules);

        // update the Referral
        $referral->update($request->input());

        return response()->json([
            'data' => $referral->fresh(),
            'message' => 'Referral has been update successfully!',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \CoderstmCore\Models\Referral  $referral
     * @return \Illuminate\Http\Response
     */
    public function destroy(Referral $referral)
    {
        $referral->forceDelete();
        return response()->json([
            'message' => 'Referral has been deleted successfully!',
        ], 200);
    }
}
