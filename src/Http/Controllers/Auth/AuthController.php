<?php

namespace Coderstm\Http\Controllers\Auth;

use Coderstm\Coderstm;
use Coderstm\Enum\AppStatus;
use Coderstm\Traits\Helpers;
use Illuminate\Http\Request;
use Coderstm\Events\UserSubscribed;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Coderstm\Notifications\UserLogin;
use Coderstm\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use Helpers;

    public function login(Request $request, $guard = 'users')
    {
        $request->validate(
            [
                'email' => "required|email|exists:{$guard},email",
                'password' => 'required',
            ],
            [
                'email.required' => 'Your email address is required.',
                'email.exists' => 'Your email address doens\'t exists.',
                'password.required' => 'Your password is required.',
            ]
        );

        if (Auth::guard($guard)->attempt($request->only(['email', 'password']))) {
            $user = $request->user($guard);

            // check user status
            if (!$user->is_active()) {
                Auth::guard($guard)->logout();
                abort(403, 'Your account has been disabled and cannot access this application. Please contact with admin.');
            }

            try {
                // create log
                $loginLog = $user->logs()->create([
                    'type' => 'login',
                    'options' => $this->location()
                ]);

                // send login alert to user if smtp configured
                $user->notify(new UserLogin($loginLog));
            } catch (\Throwable $th) {
                report($th);
            }

            // create and return user with token
            $token = $user->createToken($request->device_id, [$guard]);

            if ($user->guard == 'users') {
                $user = $user->load(['parq', 'blocked'])->toArray();
            } else if ($user->guard == 'admins') {
                $user = $user->append('modules')->toArray();
            }

            return response()->json([
                'user' => $user,
                'token' => $token->plainTextToken,
            ], 200);
        } else {
            throw ValidationException::withMessages([
                'password' => ['Your password doesn\'t match with our records.'],
            ]);
        }
    }

    public function signup(Request $request, $guard = 'users')
    {
        $rules = [
            'email' => 'required|email|unique:users',
            'title' => 'required',
            'plan' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'phone_number' => 'required',
            'line1' => 'required',
            'city' => 'required',
            'postal_code' => 'required',
            'country' => 'required',
            'interval' => 'required',
            'password' => 'required|min:6|confirmed',
        ];

        // Validate those rules
        $this->validate($request, $rules);

        $request->merge([
            'plan_id' => $request->input('plan'),
            'password' => Hash::make($request->password),
            'status' => AppStatus::PENDING->value
        ]);

        // create the user
        $user = Coderstm::$userModel::create($request->only([
            'title',
            'email',
            'plan_id',
            'first_name',
            'last_name',
            'company_name',
            'email',
            'phone_number',
            'password',
            'status',
        ]));

        // add address to the user
        $user->updateOrCreateAddress($request->input());

        event(new UserSubscribed($user));

        // create and return user with token
        $token = $user->createToken($request->device_id, [$guard]);

        $user->logs()->create([
            'type' => 'login'
        ]);

        return response()->json([
            'user' => $user,
            'token' => $token->plainTextToken,
        ], 200);
    }

    public function logout(Request $request, $guard = 'users')
    {
        try {
            Auth::guard($guard)->logout();
            $request->user()->currentAccessToken()->delete();
        } catch (\Throwable $th) {
            report($th);
        }

        return response()->json([
            'message' => 'You have been successfully logged out!'
        ], 200);
    }

    public function me($guard = 'users')
    {
        $user = current_user()->fresh([
            'address',
            'lastLogin'
        ]);

        if (guard() == 'users') {
            $user = $user->load(['parq', 'blocked'])->loadUnreadEnquiries()->toArray();
        } else if (guard() == 'admins') {
            $user = $user->append('modules')->toArray();
        }

        return response()->json($user, 200);
    }

    public function update(Request $request, $guard = 'users')
    {
        $user = current_user();

        $rules = [
            'title' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'address.line1' => 'required',
            'address.city' => 'required',
            'address.postal_code' => 'required',
            'address.country' => 'required',
            'email' => "email|unique:{$guard},email,{$user->id}",
        ];

        // Validate those rules
        $this->validate($request, $rules);

        $user->update($request->only([
            'title',
            'first_name',
            'last_name',
            'email',
            'phone_number',
        ]));

        // add address to the user
        $user->updateOrCreateAddress($request->input('address'));

        if ($request->filled('avatar')) {
            $user->avatar()->sync([
                $request->input('avatar.id') => [
                    'type' => 'avatar'
                ]
            ]);
        }

        return $this->me($guard);
    }

    public function password(Request $request, $guard = 'users')
    {
        $rules = [
            'old_password' => 'required',
            'password' => 'min:6|confirmed',
        ];

        // Validate those rules
        $this->validate($request, $rules);

        $user = current_user();
        if (Hash::check($request->old_password,  $user->password)) {
            $user->update([
                'password' => bcrypt($request->password)
            ]);
        } else {
            return response()->json([
                'message' => 'Old password doesn\'t match!'
            ], 404);
        }

        return response()->json([
            'message' => 'Password has been changed successfully!'
        ], 200);
    }
}
