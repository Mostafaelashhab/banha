<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\WaapiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    /**
     * Public — guest or auth user places an order from the menu page.
     * Sends the order to the restaurant via WAAPI (server-side), so the
     * customer never leaves the app and the message comes from Banhawy.
     */
    public function store(Business $business, Request $request): JsonResponse
    {
        if (! $business->is_active || ! $business->has_menu || ! $business->whatsapp) {
            return response()->json(['ok' => false, 'error' => 'الطلب غير متاح حالياً.'], 422);
        }

        $data = $request->validate([
            'customer_name'    => ['required', 'string', 'max:80'],
            'customer_phone'   => ['required', 'string', 'regex:/^01[0125]\d{8}$/'],
            'customer_address' => ['nullable', 'string', 'max:255'],
            'notes'            => ['nullable', 'string', 'max:500'],
            'items'            => ['required', 'array', 'min:1', 'max:50'],
            'items.*.id'       => ['required', 'integer'],
            'items.*.qty'      => ['required', 'integer', 'min:1', 'max:99'],
        ], [
            'customer_phone.regex' => 'رقم الموبايل لازم يكون مصري صحيح (11 رقم يبدأ بـ 010/011/012/015).',
            'items.required'       => 'مفيش أصناف في الطلب.',
        ]);

        // Re-fetch items from DB to get authoritative prices (never trust client)
        $ids = collect($data['items'])->pluck('id')->all();
        $menuItems = MenuItem::where('business_id', $business->id)
            ->whereIn('id', $ids)
            ->where('is_available', true)
            ->whereNotNull('price')
            ->get()
            ->keyBy('id');

        if ($menuItems->isEmpty()) {
            return response()->json([
                'ok'    => false,
                'error' => 'الأصناف اللي اخترتها مش متاحة دلوقتي. حدّث الصفحة وحاول تاني.',
            ], 422);
        }

        $rows = [];
        $subtotal = 0.0;
        foreach ($data['items'] as $line) {
            $mi = $menuItems[$line['id']] ?? null;
            if (! $mi) continue;
            $qty   = (int) $line['qty'];
            $price = (float) $mi->price;
            $lineTotal = round($qty * $price, 2);
            $subtotal += $lineTotal;
            $rows[] = [
                'menu_item_id' => $mi->id,
                'name'         => $mi->name,
                'unit_price'   => $price,
                'qty'          => $qty,
                'line_total'   => $lineTotal,
            ];
        }

        if (empty($rows)) {
            return response()->json([
                'ok'    => false,
                'error' => 'مفيش صنف صالح في طلبك.',
            ], 422);
        }

        $order = DB::transaction(function () use ($business, $data, $rows, $subtotal) {
            $order = Order::create([
                'business_id'      => $business->id,
                'user_id'          => Auth::id(),
                'customer_name'    => trim($data['customer_name']),
                'customer_phone'   => $data['customer_phone'],
                'customer_address' => $data['customer_address'] ?? null,
                'notes'            => $data['notes'] ?? null,
                'subtotal'         => round($subtotal, 2),
                'currency'         => $business->menu_currency ?? 'EGP',
                'status'           => 'pending',
                'wa_send_status'   => 'pending',
            ]);

            foreach ($rows as $r) {
                OrderItem::create(['order_id' => $order->id] + $r);
            }

            return $order;
        });

        // Send to the restaurant's WhatsApp via WAAPI (server-side)
        $waResult = WaapiService::send($business->whatsapp, $this->ownerMessage($business, $order, $rows));

        $order->update([
            'wa_send_status' => $waResult['ok']
                ? (($waResult['simulated'] ?? false) ? 'simulated' : 'sent')
                : 'failed',
            'wa_sent_at'     => $waResult['ok'] ? now() : null,
        ]);

        return response()->json([
            'ok'       => true,
            'order_id' => $order->id,
            'subtotal' => (float) $order->subtotal,
            'currency' => $order->currency,
            'sent'     => $waResult['ok'],
            'message'  => 'تم استلام طلبك! المطعم هيتواصل معاك قريب على رقمك.',
        ]);
    }

    /** Owner — list incoming orders. */
    public function ownerIndex(Business $business, Request $request)
    {
        $this->authorizeOwner($business);

        $filter = $request->query('filter', 'active');
        $q = $business->orders();
        if ($filter === 'active')         $q->whereNotIn('status', ['cancelled', 'completed']);
        elseif ($filter === 'completed')  $q->where('status', 'completed');
        elseif ($filter === 'cancelled')  $q->where('status', 'cancelled');
        elseif (in_array($filter, array_keys(Order::STATUSES), true)) $q->where('status', $filter);

        $orders = $q->with('items')->limit(200)->get();

        $counts = [
            'active'    => $business->orders()->whereNotIn('status', ['cancelled', 'completed'])->count(),
            'completed' => $business->orders()->where('status', 'completed')->count(),
            'cancelled' => $business->orders()->where('status', 'cancelled')->count(),
        ];

        return view('orders.owner-index', compact('business', 'orders', 'filter', 'counts'));
    }

    public function updateStatus(Order $order, Request $request)
    {
        $this->authorizeOwner($order->business);

        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Order::STATUSES))],
        ]);

        $order->update(['status' => $data['status']]);

        return back()->with('flash', 'تم تحديث حالة الطلب.');
    }

    private function authorizeOwner(Business $business): void
    {
        $u = Auth::user();
        $isOwner = $u && $business->owner_user_id && $u->id === $business->owner_user_id;
        $isAdmin = $u && $u->is_admin;
        abort_unless($isOwner || $isAdmin, 403);
    }

    /** Build the WhatsApp message that gets sent to the restaurant. */
    private function ownerMessage(Business $business, Order $order, array $rows): string
    {
        $currency = $order->currency;
        $lines = [];
        foreach ($rows as $r) {
            $lines[] = '• ' . $r['name'] . ' × ' . $r['qty']
                     . ' = ' . $this->fmt($r['line_total']) . ' ' . $currency;
        }

        $msg = "🔔 *طلب جديد من بنهاوي* (#{$order->id})\n";
        $msg .= "النشاط: {$business->name}\n\n";
        $msg .= "👤 {$order->customer_name}\n";
        $msg .= "📞 {$order->customer_phone}\n";
        if ($order->customer_address) {
            $msg .= "📍 {$order->customer_address}\n";
        }
        $msg .= "\n🍽 *الطلب:*\n" . implode("\n", $lines) . "\n";
        $msg .= "\n💰 *الإجمالي:* " . $this->fmt($order->subtotal) . " {$currency}\n";
        if ($order->notes) {
            $msg .= "\n📝 ملاحظات: {$order->notes}\n";
        }
        $msg .= "\n— كلّم العميل دلوقتي عشان تأكد الطلب 👌";

        return $msg;
    }

    private function fmt(float $n): string
    {
        $v = round($n, 2);
        return $v == (int) $v ? (string) (int) $v : number_format($v, 2, '.', '');
    }
}
