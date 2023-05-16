<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Booking;
use App\Traits\Helpers;
use App\Enum\AppStatus;
use Illuminate\Http\Request;
use App\Models\ClassSchedule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateParqRequest;
use App\Models\Invoice;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserController extends Controller
{
    use Helpers;

    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(User::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, User $user)
    {
        $user = $user->with($request->includes ?? []);
        $isCancelled = $request->filled('type') && $request->type == 'cancelled';

        if ($request->filled('month') || $request->filled('year')) {
            $column = $isCancelled ? 'ends_at' : 'created_at';
            $user->whereDateColumn(['month' => $request->month, 'year' => $request->year], $column);
        }

        if ($request->filled('filter')) {
            if (str($request->filter)->startsWith('email:')) {
                $filter = str($request->filter)->replace('email:', '');
                $user->where('email', 'like', "{$filter}%");
            } else {
                $user->where(DB::raw("CONCAT(first_name,' ',last_name)"), 'like', "%{$request->filter}%");
            }
        }

        if ($request->boolean('isEnquiry')) {
            $user->onlyEnquiry();
            if ($request->filled('status')) {
                $user->where('status', $request->input('status'));
            }
        } else if ($request->boolean('option')) {
            $user->whereIn('status', [AppStatus::ACTIVE, AppStatus::PENDING]);
        } else if ($isCancelled) {
            $user->onlyCancelled();;
        } else {
            $user->onlyMember();

            if ($request->boolean('status')) {
                $user->onlyActive();
            } else if ($request->status == 'late-cancellation') {
                $user->onlyLateCancellation();
            } else if ($request->status == 'no-show') {
                $user->onlyNoShow();
            } else if ($request->status == 'blocked') {
                $user->onlyBlocked();
            }

            if ($request->filled('type')) {
                $user->whereTyped($request->input('type'));
            }
        }

        if ($request->boolean('blocked')) {
            $user->onlyUnblocked();
        }

        if ($request->filled('rag')) {
            $user->where('rag', $request->rag);
        }

        if ($request->boolean('deleted')) {
            $user->onlyTrashed();
        }

        $users = $user->sortBy(optional($request)->sortBy ?? 'created_at', optional($request)->direction ?? 'desc')
            ->paginate(optional($request)->rowsPerPage ?? 15);

        return new ResourceCollection($users);
    }

    /**
     * Display a options listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function options(Request $request, User $user)
    {
        $request->merge([
            'option' => true
        ]);
        return $this->index($request, $user);
    }

    /**
     * Display a list listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request, User $user)
    {
        return $user->whereIn('id', $request->ids)->get();
    }

    /**
     * Display a enquiry listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function enquiry(Request $request, User $user)
    {
        $request->merge([
            'isEnquiry' => true,
        ]);
        return $this->index($request, $user);
    }

    /**
     * Display a finance membership listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function financeMemberships(Request $request, User $user)
    {
        $request->merge([
            'finance' => true,
            'mem_rec' => true,
        ]);
        return $this->index($request, $user);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, User $user)
    {
        $rules = [
            'email' => 'required|email|unique:users',
            'first_name' => 'required',
            'last_name' => 'required',
            'plan' => 'required_if:status,Active',
            'note' => 'required_if:status,Active',
            'password' => 'confirmed',
            'address.line1' => 'required',
            'address.city' => 'required',
            'address.postal_code' => 'required',
            'address.country' => 'required',
        ];

        // Validate those rules
        $this->validate($request, $rules);

        $request->merge([
            'password' => bcrypt($request->password ?? str()->random(6)),
            'plan_id' => $request->input('plan.id'),
        ]);

        // create the user
        $user = User::create($request->input());

        // add address to the user
        $user = $user->updateOrCreateAddress($request->input('address'));

        if ($request->filled('avatar')) {
            $user->avatar()->sync([
                $request->input('avatar.id') => [
                    'type' => 'avatar'
                ]
            ]);
        }

        return response()->json([
            'data' => $user->fresh(['address', 'notes', 'plan']),
            'message' => 'Member has been created successfully!',
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return response()->json($user->load(['notes', 'plan', 'parq']), 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {

        $rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'plan' => 'required_if:status,Active',
            'release_at' => 'required_if:status,Hold',
            'email' => "email|unique:users,email,{$user->id}",
            'password' => 'confirmed',
            'address.line1' => 'required',
            'address.city' => 'required',
            'address.postal_code' => 'required',
            'address.country' => 'required',
        ];

        // Validate those rules
        $this->validate($request, $rules);

        if ($request->filled('password')) {
            $request->merge([
                'password' => bcrypt($request->password),
            ]);
        }

        $request->merge([
            'plan_id' => $request->input('plan.id'),
        ]);

        $user->update($request->input());

        if ($request->filled('avatar')) {
            $user->avatar()->sync([
                $request->input('avatar.id') => [
                    'type' => 'avatar'
                ]
            ]);
        }

        if ($request->filled('special_note')) {
            $user->notes()->create([
                'type' => 'notes',
                'message' => $request->special_note,
            ]);
        }

        if ($request->filled('ends_at')) {
            $user = $user->updateEndsAt($request->ends_at);
        }

        $user->updateOrCreateAddress($request->input('address'));

        return response()->json([
            'data' => $user->fresh(['address', 'notes', 'plan', 'parq']),
            'message' => 'Member has been update successfully!',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json([
            'message' => 'Member has been deleted successfully!',
        ], 200);
    }

    /**
     * Remove the selected resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy_selected(Request $request, User $user)
    {
        $this->validate($request, [
            'items' => 'required',
        ]);
        $user->whereIn('id', $request->items)->each(function ($item) {
            $item->delete();
        });
        return response()->json([
            'message' => 'Users has been deleted successfully!',
        ], 200);
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        User::onlyTrashed()
            ->where('id', $id)->each(function ($item) {
                $item->restore();
            });
        return response()->json([
            'message' => 'User has been restored successfully!',
        ], 200);
    }

    /**
     * Remove the selected resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function restore_selected(Request $request, User $user)
    {
        $this->validate($request, [
            'items' => 'required',
        ]);
        $user->onlyTrashed()
            ->whereIn('id', $request->items)->each(function ($item) {
                $item->restore();
            });
        return response()->json([
            'message' => 'Users has been restored successfully!',
        ], 200);
    }

    /**
     * Send reset password request to specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function resetPasswordRequest(Request $request, User $user)
    {
        $status = Password::sendResetLink([
            'email' => $user->email,
        ]);

        return response()->json([
            'status' => $status,
            'message' => 'Password reset link sent successfully!',
        ], 200);
    }

    /**
     * Change active of specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function changeActive(Request $request, User $user)
    {
        $user->update([
            'is_active' => !$user->is_active
        ]);

        return response()->json([
            'message' => $user->is_active ? 'Member marked as active successfully!' : 'Member marked as deactivated successfully!',
        ], 200);
    }

    /**
     * Change checked specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function checked(Request $request, User $user)
    {
        $user->update([
            'checked' => !$user->checked
        ]);

        return response()->json([
            'message' => $user->checked ? 'Member marked as checked successfully!' : 'Member marked as unchecked successfully!',
        ], 200);
    }

    /**
     * Change request_parq of specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function requestParq(Request $request, User $user)
    {
        $user->update([
            'request_parq' => !$user->request_parq
        ]);

        return response()->json([
            'message' => $user->request_parq ? 'Member M-PARQ marked as checked successfully!' : 'Member M-PARQ marked as unchecked successfully!',
        ], 200);
    }

    /**
     * Change request_avatar of specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function requestAvatar(Request $request, User $user)
    {
        $user->update([
            'request_avatar' => !$user->request_avatar
        ]);

        return response()->json([
            'message' => $user->request_avatar ? 'Member M-Pic marked as checked successfully!' : 'Member M-Pic marked as unchecked successfully!',
        ], 200);
    }

    /**
     * Display a schedules listing of specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function schedules(Request $request, User $user)
    {
        ClassSchedule::resolveRelationUsing('booking', function ($schedule) use ($user) {
            return $schedule->hasOne(Booking::class, 'schedule_id')->withOnly([])->where('user_id', $user->id);
        });

        $classes = $user->schedules()->with('booking');

        if ($request->filled('filter')) {
            $classes->whereHas('class', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->filter}%");
            });
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $date_from = Carbon::parse($request->input('date_from'))->format('Y-m-d');
            $date_to = Carbon::parse($request->input('date_to'))->format('Y-m-d');
            $classes->whereRaw('date_at BETWEEN ? AND ?',  [$date_from, $date_to]);
        } else if ($request->filled('date')) {
            $date = Carbon::parse($request->input('date'));
            $classes->whereRaw('date_at = ?', [$date]);
        }

        if ($request->filled('status')) {
            switch ($request->status) {
                case 'active':
                    $classes->whereRaw('date_at >= CURDATE()')
                        ->whereHas('bookings', function ($q) use ($user) {
                            $q->where('user_id', $user->id)
                                ->onlyNotCanceled();
                        });
                    break;

                case 'cancelled':
                    $classes->whereHas('bookings', function ($q) use ($user) {
                        $q->where('user_id', $user->id)
                            ->onlyCanceled();
                    });
                    break;

                case 'late-cancellation':
                    $classes->whereHas('bookings', function ($q) use ($user) {
                        $q->where('user_id', $user->id)
                            ->onlyLateCancellation();
                    });
                    break;

                case 'attended':
                    $classes->whereHas('bookings', function ($q) use ($user) {
                        $q->where('user_id', $user->id)
                            ->onlyAttended();
                    });
                    break;

                case 'noshow':
                    $classes->whereHas('bookings', function ($q) use ($user) {
                        $q->where('user_id', $user->id)
                            ->onlyNoShow();
                    });
                    break;
            }
        }

        $classes = $classes->orderBy(optional($request)->sortBy ?? 'created_at', optional($request)->direction ?? 'desc')
            ->paginate(optional($request)->rowsPerPage ?? 15);
        return new ResourceCollection($classes);
    }

    /**
     * Create notes for specified resource from storage.
     *
     * @param  \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function notes(Request $request, User $user)
    {

        if ($request->filled('rag')) {
            $user->update($request->only('rag'));
        }

        if ($request->filled('message')) {
            $request->merge([
                'type' => 'notes',
            ]);

            $note = $user->notes()->create($request->input());

            return response()->json([
                'data' => $note->load('admin'),
                'message' => 'Note has been added successfully!',
            ], 200);
        } else {
            return response()->json([
                'data' => null,
                'message' => 'Note has been added successfully!',
            ], 200);
        }
    }

    public function updateParq(UpdateParqRequest $request, User $user)
    {
        return response()->json([
            'data' => $user->updateOrCreateParq($request->input()),
            'message' => 'Parq has been updated successfully!',
        ], 200);
    }

    /**
     * Change block of specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function changeBlock(Request $request, User $user)
    {
        $user->updateOrCreateBlocked([
            'disabled' => $user->has_blocked
        ]);

        return response()->json([
            'message' => $user->has_blocked ? 'Member marked as unblocked successfully!' : 'Member marked as blocked successfully!',
        ], 200);
    }

    public function markAsPaid(Request $request, User $user)
    {
        try {
            $subscription = $user->subscription();
            if ($subscription->pastDue() || $user->hasIncompletePayment()) {
                $invoice = $subscription->latestInvoice();
                $invoice->pay([
                    'paid_out_of_band' => true
                ]);
            } else if ($user->onTrial()) {
                $user->creditBalance($subscription->upcomingInvoice()->amount_due, $request->note ?? 'Cash');
                $subscription->endTrial();
            }
        } catch (\Throwable $th) {
            throw $th;
        } finally {
            $stripeSubscription = $subscription->asStripeSubscription();
            $subscription->update([
                'stripe_status' => $stripeSubscription->status
            ]);

            // Create invoice to application database
            $invoice = $subscription->latestInvoice();
            Invoice::createFromStripe($invoice);
        }

        return response()->json([
            'data' => $user->fresh(),
            'message' => 'Subscription payment has been received.'
        ], 200);
    }
}
