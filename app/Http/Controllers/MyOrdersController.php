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
    /**
     * "اطلب تاني" — replay a past order. We stash {menu_item_id, qty} pairs
     * in the session and redirect to the restaurant's menu page; the page's
     * cart JS picks them up and pre-fills the cart using the **current**
     * menu prices (snapshot from the live DOM, not the old order's price).
     *
     * Items that are no longer on the menu / no longer available are
     * silently dropped — the UI banner tells the user.
     */
    public function reorder(Order $order, Request $request)
    {
        abort_unless($order->user_id && $order->user_id === Auth::id(), 403);

        $items = $order->items()
            ->whereNotNull('menu_item_id')
            ->get(['menu_item_id', 'qty'])
            ->map(fn ($it) => ['id' => (int) $it->menu_item_id, 'qty' => (int) $it->qty])
            ->values()
            ->all();

        if (empty($items)) {
            return redirect()
                ->route('menu.public', $order->business)
                ->with('flash', 'مفيش أصناف من الأوردر القديم لسه متاحة.');
        }

        session()->flash('reorder_request', [
            'items'    => $items,
            'order_id' => $order->id,
        ]);

        return redirect()->route('menu.public', $order->business);
    }

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
