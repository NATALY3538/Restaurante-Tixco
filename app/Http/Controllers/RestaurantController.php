<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Modifier;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemModifier;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductInventory;
use App\Models\InventoryMovement;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RestaurantController extends Controller
{
    // GET /api/categories-products
    public function categoriesProducts()
    {
        $categories = Category::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($cat) {
                return [
                    'categoryId' => $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                    'description' => $cat->description,
                    'sortOrder' => $cat->sort_order,
                    'products' => $cat->products()
                        ->where('is_active', true)
                        ->get()
                        ->map(function ($p) {
                            return [
                                'id' => $p->id,
                                'categoryId' => $p->category_id,
                                'name' => $p->name,
                                'slug' => $p->slug,
                                'description' => $p->description,
                                'price' => (float)$p->price,
                                'imageUrl' => $p->image_url,
                                'estimatedPreparationMinutes' => $p->estimated_preparation_minutes,
                                'isVegetarian' => (bool)$p->is_vegetarian,
                                'isSpicy' => (bool)$p->is_spicy,
                                'isGlutenFree' => (bool)$p->is_gluten_free,
                                'isFeatured' => (bool)$p->is_featured,
                                'isActive' => (bool)$p->is_active,
                                'images' => \Illuminate\Support\Facades\Schema::hasTable('product_images') ? $p->images()->orderBy('sort_order')->get() : []
                            ];
                        })
                ];
            });

        return response()->json($categories);
    }

    // GET /api/products
    public function products(Request $request)
    {
        $query = Product::with('category')->where('is_active', true);

        if ($request->has('categorySlug')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->query('categorySlug'));
            });
        }

        if ($request->has('search')) {
            $search = strtolower($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where(DB::raw('LOWER(name)'), 'like', "%{$search}%")
                  ->orWhere(DB::raw('LOWER(description)'), 'like', "%{$search}%");
            });
        }

        return response()->json($query->get());
    }

    // GET /api/products/{idOrSlug}
    public function productDetail($idOrSlug)
    {
        $product = Product::with(['images', 'modifierGroups.modifiers'])
            ->where('is_active', true)
            ->where(function ($query) use ($idOrSlug) {
                $query->where('id', $idOrSlug)
                      ->orWhere('slug', $idOrSlug);
            })
            ->first();

        if (!$product) {
            return response()->json(['message' => 'Platillo no encontrado'], 404);
        }

        return response()->json($product);
    }

    // GET /api/tables
    public function tables()
    {
        $tables = RestaurantTable::with('serviceArea')->get();
        return response()->json($tables);
    }

    // GET /api/tables/qr/{token}
    public function getTableByQr($token)
    {
        $table = RestaurantTable::with('serviceArea')->where('qr_token', $token)->first();
        if (!$table) {
            return response()->json(['message' => 'Mesa no encontrada'], 404);
        }
        return response()->json($table);
    }

    // GET /api/payment-methods
    public function paymentMethods()
    {
        $methods = PaymentMethod::where('is_active', true)->get();
        return response()->json($methods);
    }

    // POST /api/pedidos
    public function placeOrder(Request $request)
    {
        $dto = $request->validate([
            'order.customerName' => 'required|string',
            'order.customerPhone' => 'required|string',
            'order.customerEmail' => 'nullable|email',
            'order.orderType' => 'required|string',
            'order.restaurantTableId' => 'nullable|integer',
            'order.addressLine1' => 'nullable|string',
            'order.neighborhood' => 'nullable|string',
            'order.city' => 'nullable|string',
            'order.state' => 'nullable|string',
            'order.postalCode' => 'nullable|string',
            'order.deliveryNotes' => 'nullable|string',
            'order.paymentMethodId' => 'required|integer',
            'order.specialNotes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.productId' => 'required|integer',
            'items.*.productName' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unitPrice' => 'required|numeric',
            'items.*.specialNote' => 'nullable|string',
            'items.*.modifiers' => 'nullable|array',
            'items.*.modifiers.*.modifierId' => 'required|integer',
            'items.*.modifiers.*.modifierName' => 'required|string',
            'items.*.modifiers.*.priceDelta' => 'required|numeric',
        ]);

        return DB::transaction(function () use ($request) {
            $orderData = $request->input('order');
            $itemsData = $request->input('items');

            // Find or create customer
            $customer = Customer::firstOrCreate(
                ['phone' => $orderData['customerPhone']],
                [
                    'name' => $orderData['customerName'],
                    'email' => $orderData['customerEmail'] ?? null
                ]
            );

            // Save address if new delivery address
            if ($orderData['orderType'] === 'delivery' && !empty($orderData['addressLine1'])) {
                $addrExists = CustomerAddress::where('customer_id', $customer->id)
                    ->where('address_line_1', $orderData['addressLine1'])
                    ->exists();

                if (!$addrExists) {
                    CustomerAddress::create([
                        'customer_id' => $customer->id,
                        'label' => 'Dirección de entrega',
                        'recipient_name' => $orderData['customerName'],
                        'phone' => $orderData['customerPhone'],
                        'address_line_1' => $orderData['addressLine1'],
                        'neighborhood' => $orderData['neighborhood'] ?? null,
                        'city' => $orderData['city'] ?? null,
                        'state' => $orderData['state'] ?? null,
                        'postal_code' => $orderData['postalCode'] ?? null,
                        'delivery_notes' => $orderData['deliveryNotes'] ?? null,
                    ]);
                }
            }

            // Calculate totals
            $subtotal = 0;
            $modifiersTotal = 0;

            foreach ($itemsData as $item) {
                $qty = $item['quantity'];
                $price = $item['unitPrice'];
                $subtotal += $price * $qty;

                $mods = $item['modifiers'] ?? [];
                foreach ($mods as $mod) {
                    $modifiersTotal += $mod['priceDelta'] * $qty;
                }
            }

            $deliveryFee = $orderData['orderType'] === 'delivery' ? 30.00 : 0.00;
            $total = $subtotal + $modifiersTotal + $deliveryFee;

            // Create Order
            $order = Order::create([
                'customer_id' => $customer->id,
                'restaurant_table_id' => $orderData['restaurantTableId'] ?? null,
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'order_type' => $orderData['orderType'],
                'status' => 'confirmed',
                'payment_status' => 'pending',
                'subtotal' => $subtotal,
                'modifiers_total' => $modifiersTotal,
                'delivery_fee' => $deliveryFee,
                'total' => $total,
                'customer_notes' => $orderData['specialNotes'] ?? null,
                'requested_at' => now(),
            ]);

            // Update assigned table status to occupied for Dine-in orders
            if ($order->restaurant_table_id) {
                $table = RestaurantTable::find($order->restaurant_table_id);
                if ($table) {
                    $table->status = 'occupied';
                    $table->save();
                }
            }

            // Add Items and Modifiers, and Decrement Stock
            foreach ($itemsData as $itemData) {
                $qty = $itemData['quantity'];
                $price = $itemData['unitPrice'];
                
                $itemModsTotal = 0;
                $mods = $itemData['modifiers'] ?? [];
                foreach ($mods as $m) {
                    $itemModsTotal += $m['priceDelta'];
                }

                $itemTotal = ($price + $itemModsTotal) * $qty;

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $itemData['productId'],
                    'product_name' => $itemData['productName'],
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'modifiers_total' => $itemModsTotal * $qty,
                    'total' => $itemTotal,
                    'special_note' => $itemData['specialNote'] ?? '',
                ]);

                foreach ($mods as $m) {
                    OrderItemModifier::create([
                        'order_item_id' => $orderItem->id,
                        'modifier_id' => $m['modifierId'],
                        'modifier_name' => $m['modifierName'],
                        'price_delta' => $m['priceDelta'],
                        'quantity' => 1,
                        'total' => $m['priceDelta'] * $qty
                    ]);
                }

                // Decrement stock in inventories
                $inventory = ProductInventory::where('product_id', $itemData['productId'])->first();
                if ($inventory) {
                    $inventory->stock = max(0, $inventory->stock - $qty);
                    $inventory->save();

                    // Register movement
                    InventoryMovement::create([
                        'product_id' => $itemData['productId'],
                        'quantity_delta' => -$qty,
                        'type' => 'sale',
                        'notes' => "Venta en pedido #" . $order->order_number
                    ]);
                }
            }

            // Create Payment
            Payment::create([
                'order_id' => $order->id,
                'payment_method_id' => $orderData['paymentMethodId'],
                'status' => 'pending',
                'amount' => $total,
                'currency' => 'MXN',
                'reference' => 'REF-' . strtoupper(Str::random(10)),
            ]);

            return response()->json($order, 201);
        });
    }

    // GET /api/orders/customer/{phone}
    public function customerOrders($phone)
    {
        $orders = Order::with(['items.modifiers', 'payment.paymentMethod'])
            ->whereHas('customer', function ($q) use ($phone) {
                $q->where('phone', $phone);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    // POST /api/reservations
    public function makeReservation(Request $request)
    {
        $data = $request->validate([
            'customerName' => 'required|string',
            'customerPhone' => 'required|string',
            'customerEmail' => 'nullable|email',
            'reservationDate' => 'required|date',
            'reservationTime' => 'required|string',
            'partySize' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $customer = Customer::firstOrCreate(
            ['phone' => $data['customerPhone']],
            [
                'name' => $data['customerName'],
                'email' => $data['customerEmail'] ?? null
            ]
        );

        $res = Reservation::create([
            'customer_id' => $customer->id,
            'reservation_code' => 'RES-' . strtoupper(Str::random(8)),
            'customer_name' => $data['customerName'],
            'customer_phone' => $data['customerPhone'],
            'customer_email' => $data['customerEmail'] ?? null,
            'reservation_date' => $data['reservationDate'],
            'reservation_time' => $data['reservationTime'],
            'party_size' => $data['partySize'],
            'status' => 'confirmed',
            'notes' => $data['notes'] ?? null,
        ]);

        return response()->json($res, 201);
    }

    // GET /api/reservations/customer/{phone}
    public function customerReservations($phone)
    {
        $res = Reservation::whereHas('customer', function ($q) use ($phone) {
                $q->where('phone', $phone);
            })
            ->orderBy('reservation_date', 'desc')
            ->orderBy('reservation_time', 'desc')
            ->get();

        return response()->json($res);
    }

    // GET /api/customers/{phone}/addresses
    public function getAddresses($phone)
    {
        $customer = Customer::where('phone', $phone)->first();
        if (!$customer) {
            return response()->json([]);
        }

        $addresses = CustomerAddress::where('customer_id', $customer->id)->get();
        return response()->json($addresses);
    }

    // POST /api/customers/{phone}/addresses
    public function addAddress(Request $request, $phone)
    {
        $data = $request->validate([
            'label' => 'required|string',
            'recipientName' => 'required|string',
            'phone' => 'required|string',
            'addressLine1' => 'required|string',
            'neighborhood' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'postalCode' => 'required|string',
            'deliveryNotes' => 'nullable|string',
        ]);

        $customer = Customer::where('phone', $phone)->first();
        if (!$customer) {
            $customer = Customer::create([
                'phone' => $phone,
                'name' => $data['recipientName'],
            ]);
        }

        $address = CustomerAddress::create([
            'customer_id' => $customer->id,
            'label' => $data['label'],
            'recipient_name' => $data['recipientName'],
            'phone' => $data['phone'],
            'address_line_1' => $data['addressLine1'],
            'neighborhood' => $data['neighborhood'],
            'city' => $data['city'],
            'state' => $data['state'],
            'postal_code' => $data['postalCode'],
            'delivery_notes' => $data['deliveryNotes'] ?? null,
        ]);

        return response()->json($address, 201);
    }

    // DELETE /api/customers/addresses/{id}
    public function deleteAddress($id)
    {
        $address = CustomerAddress::findOrFail($id);
        $address->delete();

        return response()->json(['message' => 'Dirección eliminada correctamente']);
    }
}
