<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Plan;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function getPlans()
    {
        return Plan::all();
    }

    public function createOrder(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);

        $order = Order::create([
            'user_id' => $request->user()->id,
            'plan_id' => $plan->id,
            'total_price' => $plan->price,
            'status' => 'pending',
        ]);

        return response()->json(['order' => $order], 201);
    }

    public function createPayment(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = Order::findOrFail($validated['order_id']);

        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $payment = Payment::create([
            'order_id' => $order->id,
            'snap_token' => 'DEMO-TOKEN-' . time(),
            'payment_url' => null,
            'status' => 'success',
        ]);

        $order->update(['status' => 'paid']);
        $order->user->update(['is_premium' => true]);

        return response()->json([
            'payment' => $payment,
            'message' => 'Payment success - Demo mode',
            'status' => 'success',
            'is_demo' => true
        ]);
    }

    public function midtransCallback(Request $request)
    {
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$curlOptions = [
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ];

        $notification = new \Midtrans\Notification();

        $orderId = explode('-', $notification->order_id)[1];
        $order = Order::find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($notification->transaction_status == 'settlement' || $notification->transaction_status == 'capture') {
            $order->update(['status' => 'paid']);
            $order->user->update(['is_premium' => true]);
            $order->payment->update(['status' => 'success']);
        } elseif ($notification->transaction_status == 'deny' || $notification->transaction_status == 'expire' || $notification->transaction_status == 'cancel') {
            $order->update(['status' => 'failed']);
            $order->payment->update(['status' => 'failed']);
        }

        return response()->json(['message' => 'Callback processed']);
    }
}
