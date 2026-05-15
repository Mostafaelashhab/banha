<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Customer-facing order tracking.
 *
 * Logged-in users see every order placed with their account (including guest
 * orders later linked via phone match — see future work). For now we filter
 * by user_id only; guest orders aren't visible since they have no account.
 */
class MyOrdersController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'active');

        $q = Order::query()
            ->where('user_id', Auth::id())
            ->with(['business:id,name,photo_url,whatsapp,phone,category', 'items', 'area:id,name,parent']);

        if ($filter === 'active')         $q->whereNotIn('status', ['cancelled', 'completed']);
        elseif ($filter === 'completed')  $q->where('status', 'completed');
        elseif ($filter === 'cancelled')  $q->where('status', 'cancelled');

        $orders = $q->latest()->limit(100)->get();

        $counts = [
            'active'    => Order::where('user_id', Auth::id())->whereNotIn('status', ['cancelled', 'completed'])->count(),
            'completed' => Order::where('user_id', Auth::id())->where('status', 'completed')->count(),
            'cancelled' => Order::where('user_id', Auth::id())->where('status', 'cancelled')->count(),
        ];

        return view('orders.my-index', compact('orders', 'filter', 'counts'));
    }
}
