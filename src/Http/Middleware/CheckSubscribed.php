<?php

namespace Coderstm\Http\Middleware;

use Closure;
use Coderstm\Coderstm;

class CheckSubscribed
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $subscribed = false)
    {
        $user = $this->user();
        if ($user->subscribed()) {
            return $next($request);
        } else if ($user->subscription() && $user->subscription()->cancelled()) {
            return response()->json([
                'cancelled' => true,
                'message' => "Your Subscriptin will end on {$user->subscription()->ends_at->format('D d M Y')}"
            ], 200);
        } else {
            return response()->json([
                'subscribed' => $subscribed,
                'message' => 'Sorry, it seems that you are not currently subscribed to any plan. Please subscribe to a plan to continue accessing our content and features. Thank you!'
            ], 403);
        }
    }

    private function user()
    {
        if (request()->filled('user_id') && is_admin()) {
            return Coderstm::$userModel::findOrFail(request()->user_id);
        }
        return current_user();
    }
}
