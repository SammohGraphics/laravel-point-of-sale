<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Order;
use App\Models\Product;
use App\Models\OrderDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Redirect;
use Barryvdh\DomPDF\Facade\Pdf;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Display a listing of pending orders.
     */
    public function pendingOrders()
    {
        $orders = $this->getPaginatedOrders('pending');

        return view('orders.pending-orders', compact('orders'));
    }

    /**
     * Display a listing of complete orders.
     */
    public function completeOrders()
    {
        $orders = $this->getPaginatedOrders('complete');

        return view('orders.complete-orders', compact('orders'));
    }

    /**
     * Get paginated orders based on status.
     */
    private function getPaginatedOrders(string $status)
    {
        $row = (int) request('row', 10);
        if ($row < 1 || $row > 100) {
            abort(400, 'The per-page parameter must be an integer between 1 and 100.');
        }

        return Order::where('order_status', $status)->sortable()->paginate($row);
    }

    /**
     * Display stock management page.
     */
    public function stockManage()
    {
        $row = (int) request('row', 10);
        if ($row < 1 || $row > 100) {
            abort(400, 'The per-page parameter must be an integer between 1 and 100.');
        }

        $products = Product::with(['category', 'supplier'])
            ->filter(request(['search']))
            ->sortable()
            ->paginate($row)
            ->appends(request()->query());

        return view('stock.index', compact('products'));
    }

    /**
     * Store a newly created order.
     */
    public function storeOrder(Request $request)
    {
        $validatedData = $this->validateOrderData($request);

        DB::beginTransaction();
        try {
            $invoice_no = $this->generateInvoiceNo();
            $validatedData['order_date'] = Carbon::now()->format('Y-m-d');
            $validatedData['order_status'] = 'pending';
            $validatedData['total_products'] = Cart::count();
            $validatedData['sub_total'] = Cart::subtotal();
            $validatedData['vat'] = Cart::tax();
            $validatedData['invoice_no'] = $invoice_no;
            $validatedData['total'] = Cart::total();
            $validatedData['due'] = $validatedData['total'] - $validatedData['pay'];
            $validatedData['created_at'] = Carbon::now();

            $order_id = Order::create($validatedData)->id;

            $this->storeOrderDetails($order_id);

            DB::commit();

            // Clear the cart
            Cart::destroy();

            return Redirect::route('dashboard')->with('success', 'Order has been created!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Order creation failed: " . $e->getMessage());
            return Redirect::back()->with('error', 'Failed to create order.');
        }
    }

    /**
     * Validate order data.
     */
    private function validateOrderData(Request $request)
    {
        return $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'payment_status' => 'required|string',
            'pay' => 'nullable|numeric|min:0',
            'due' => 'nullable|numeric|min:0',
        ]);
    }

    /**
     * Generate unique invoice number.
     */
    private function generateInvoiceNo()
    {
        return IdGenerator::generate([
            'table' => 'orders',
            'field' => 'invoice_no',
            'length' => 10,
            'prefix' => 'INV-'
        ]);
    }

    /**
     * Store order details.
     */
    private function storeOrderDetails($order_id)
    {
        $contents = Cart::content();
        foreach ($contents as $content) {
            OrderDetails::create([
                'order_id' => $order_id,
                'product_id' => $content->id,
                'quantity' => $content->qty,
                'unitcost' => $content->price,
                'total' => $content->total,
                'created_at' => Carbon::now(),
            ]);
        }
    }

    /**
     * Display the order details.
     */
    public function orderDetails(Int $order_id)
    {
        $order = Order::findOrFail($order_id);
        $orderDetails = OrderDetails::with('product')
            ->where('order_id', $order_id)
            ->orderBy('id', 'DESC')
            ->get();

        return view('orders.details-order', compact('order', 'orderDetails'));
    }

    /**
     * Update the order status to complete and adjust stock.
     */
    public function updateStatus(Request $request)
    {
        DB::beginTransaction();
        try {
            $order = Order::findOrFail($request->id);
            $this->reduceProductStock($order->id);

            $order->update(['order_status' => 'complete']);

            DB::commit();
            return Redirect::route('order.pendingOrders')->with('success', 'Order has been completed!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to update order status: " . $e->getMessage());
            return Redirect::back()->with('error', 'Failed to complete order.');
        }
    }

    /**
     * Reduce product stock when order is completed.
     */
    private function reduceProductStock($order_id)
    {
        $products = OrderDetails::where('order_id', $order_id)->get();

        foreach ($products as $product) {
            Product::where('id', $product->product_id)
                ->update(['product_store' => DB::raw('product_store - ' . $product->quantity)]);
        }
    }

    /**
     * Display pending due orders.
     */
    public function pendingDue()
    {
        $orders = $this->getPaginatedDueOrders();

        return view('orders.pending-due', compact('orders'));
    }

    /**
     * Get paginated due orders.
     */
    private function getPaginatedDueOrders()
    {
        $row = (int) request('row', 10);
        if ($row < 1 || $row > 100) {
            abort(400, 'The per-page parameter must be an integer between 1 and 100.');
        }

        return Order::where('due', '>', 0)->sortable()->paginate($row);
    }

    /**
     * Update the due payment for an order.
     */
    public function updateDue(Request $request)
    {
        $validatedData = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'due' => 'required|numeric|min:0',
        ]);

        $order = Order::findOrFail($request->order_id);

        $paid_due = $order->due - $validatedData['due'];
        $paid_pay = $order->pay + $validatedData['due'];

        $order->update([
            'due' => $paid_due,
            'pay' => $paid_pay,
        ]);

        return Redirect::route('order.pendingDue')->with('success', 'Due Amount Updated Successfully!');
    }

    /**
     * Download the invoice for an order.
     */
    public function invoiceDownload(Int $order_id)
    {
        $order = Order::findOrFail($order_id);
        $orderDetails = OrderDetails::with('product')
            ->where('order_id', $order_id)
            ->orderBy('id', 'DESC')
            ->get();

        return view('orders.invoice-order', compact('order', 'orderDetails'));
    }
}
