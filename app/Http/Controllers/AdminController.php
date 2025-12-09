<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function activatePremium(Request $request)
    {
        $validated = $request->validate([
            'user_email' => 'required|email|exists:users,email',
            'plan_id' => 'required|exists:plans,id',
            'voucher_code' => 'nullable|string',
        ]);

        $user = User::where('email', $validated['user_email'])->firstOrFail();
        $plan = Plan::findOrFail($validated['plan_id']);

        $discount = 0;
        if (isset($validated['voucher_code'])) {
            $voucher = Voucher::where('code', $validated['voucher_code'])
                ->where('is_active', true)
                ->first();

            if ($voucher) {
                if ($voucher->discount_type === 'percentage') {
                    $discount = ($plan->price * $voucher->discount_value) / 100;
                } else {
                    $discount = $voucher->discount_value;
                }
                $voucher->increment('used_count');
            }
        }

        $activeSubscription = UserSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('end_date', '>=', now())
            ->first();

        if ($activeSubscription) {
            $startDate = Carbon::parse($activeSubscription->end_date)->addDay();
        } else {
            $startDate = now();
        }

        $endDate = $startDate->copy()->addDays($plan->duration_days);

        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'active',
        ]);

        $user->update([
            'is_premium' => true,
            'premium_expires_at' => $endDate,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Premium activated successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_premium' => true,
            ],
            'subscription' => $subscription,
            'discount' => $discount,
        ], 201);
    }

    public function premiumCount()
    {
        $totalPremium = User::where('is_premium', 1)->count();
        $activePremium = User::where('is_premium', 1)
            ->where(function($query) {
                $query->whereNull('premium_expires_at')
                      ->orWhere('premium_expires_at', '>', now());
            })
            ->count();
        $expiredPremium = User::where('is_premium', 1)
            ->whereNotNull('premium_expires_at')
            ->where('premium_expires_at', '<=', now())
            ->count();

        return response()->json([
            'count' => $totalPremium,
            'active' => $activePremium,
            'expired' => $expiredPremium,
        ]);
    }

    public function listUsers()
    {
        $users = User::select(['id', 'name', 'email', 'is_premium', 'premium_expires_at', 'role', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['users' => $users]);
    }
}
