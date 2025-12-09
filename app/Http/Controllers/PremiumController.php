<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\UserSubscription;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PremiumController extends Controller
{
    public function getPackages()
    {
        return Plan::all();
    }

    public function purchase(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'voucher_code' => 'nullable|string',
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);
        $user = $request->user();
        $finalPrice = $plan->price;
        $discount = 0;

        if (isset($validated['voucher_code'])) {
            $voucher = Voucher::where('code', $validated['voucher_code'])
                ->where('is_active', true)
                ->first();

            if (!$voucher) {
                return response()->json(['message' => 'Invalid voucher code'], 400);
            }

            if ($voucher->expires_at && $voucher->expires_at->isPast()) {
                return response()->json(['message' => 'Voucher has expired'], 400);
            }

            if ($voucher->used_count >= $voucher->max_uses) {
                return response()->json(['message' => 'Voucher usage limit reached'], 400);
            }

            if ($voucher->discount_type === 'percentage') {
                $discount = ($plan->price * $voucher->discount_value) / 100;
            } else {
                $discount = $voucher->discount_value;
            }

            $finalPrice = max(0, $plan->price - $discount);
            $voucher->increment('used_count');
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
            'subscription' => $subscription,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_premium' => true,
            ],
            'original_price' => $plan->price,
            'discount' => $discount,
            'final_price' => $finalPrice,
            'message' => 'Premium subscription activated successfully'
        ], 201);
    }

    public function validateVoucher(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'plan_id' => 'required|exists:plans,id',
        ]);

        $voucher = Voucher::where('code', $validated['code'])
            ->where('is_active', true)
            ->first();

        if (!$voucher) {
            return response()->json(['message' => 'Invalid voucher code', 'valid' => false], 400);
        }

        if ($voucher->expires_at && $voucher->expires_at->isPast()) {
            return response()->json(['message' => 'Voucher has expired', 'valid' => false], 400);
        }

        if ($voucher->used_count >= $voucher->max_uses) {
            return response()->json(['message' => 'Voucher usage limit reached', 'valid' => false], 400);
        }

        $plan = Plan::findOrFail($validated['plan_id']);
        $discount = 0;

        if ($voucher->discount_type === 'percentage') {
            $discount = ($plan->price * $voucher->discount_value) / 100;
        } else {
            $discount = $voucher->discount_value;
        }

        $finalPrice = max(0, $plan->price - $discount);

        return response()->json([
            'valid' => true,
            'voucher' => [
                'code' => $voucher->code,
                'discount_type' => $voucher->discount_type,
                'discount_value' => $voucher->discount_value,
                'max_uses' => $voucher->max_uses,
                'used_count' => $voucher->used_count,
                'expires_at' => $voucher->expires_at,
            ],
            'original_price' => $plan->price,
            'discount' => $discount,
            'final_price' => $finalPrice,
        ]);
    }

    public function getUserSubscription(Request $request)
    {
        $user = $request->user();
        
        $subscription = UserSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('end_date', '>=', now())
            ->with('plan')
            ->first();

        if (!$subscription) {
            return response()->json([
                'is_premium' => false,
                'subscription' => null,
            ]);
        }

        return response()->json([
            'is_premium' => true,
            'subscription' => $subscription,
        ]);
    }
}
