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
use Illuminate\Support\Facades\Log;
use App\Http\Requests\StoreOrderRequest;

class OrderController extends Controller
{
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETE = 'complete';

    /**
     * Display a listing of pending orders.
     */
    public function pendingOrders()
    {
        $orders = $this->getPaginatedOrders(self::STATUS_PENDING);
        return view('orders.pending-orders', compact('orders'));
    }

    /**
     * Display a listing of complete orders.
     */
    public function completeOrders()
    {
        $orders = $this->getPaginatedOrders(self::STATUS_COMPLETE);
        return view('orders.complete-orders', compact('orders'));
    }

    /**
     * Get paginated orders based on status.
     */
    private function getPaginatedOrders(string $status)
    {
        $row = $this->validatePaginationRow();
        return Order::where('order_status', $status)->sortable()->paginate($row);
    }

    /**
     * Validate pagination row request and return the row value.
     */
    private function validatePaginationRow()
    {
        $row = (int) request('row', 10);
        if ($row < 1 || $row > 100) {
            abort(400, 'The per-page parameter must be an integer between 1 and 100.');
        }
        return $row;
    }

    /**
     * Display stock management page.
     */
    public function stockManage()
    {
        $row = $this->validatePaginationRow();
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
    public function storeOrder(StoreOrderRequest $request)
    {
        DB::beginTransaction();
        try {
            $invoice_no = $this->generateInvoiceNo();
            $data = $request->validated();
            $data['order_date'] = now()->format('Y-m-d');
            $data['order_status'] = self::STATUS_PENDING;
            $data['total_products'] = Cart::count();
            $data['sub_total'] = Cart::subtotal();
            $data['vat'] = Cart::tax();
            $data['invoice_no'] = $invoice_no;
            $data['total'] = Cart::total();
            $data['due'] = $data['total'] - $data['pay'];
            $data['created_at'] = now();

            $order = Order::create($data);
            $this->storeOrderDetails($order->id);

            DB::commit();
            Cart::destroy(); // Clear the cart after order is created.

            return Redirect::route('dashboard')->with('success', 'Order has been created!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Order creation failed: {$e->getMessage()}");
            return Redirect::back()->with('error', 'Failed to create order.');
        }
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
                'created_at' => now(),
            ]);
        }
    }

    /**
     * Display the order details.
     */
    public function orderDetails(int $order_id)
    {
        $order = Order::findOrFail($order_id);
        $orderDetails = OrderDetails::with('product')
            ->where('order_id', $order_id)
            ->orderByDesc('id')
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
            $order->update(['order_status' => self::STATUS_COMPLETE]);

            DB::commit();
            return Redirect::route('order.pendingOrders')->with('success', 'Order has been completed!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to update order status: {$e->getMessage()}");
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
            $productModel = Product::find($product->product_id);
            if ($productModel->product_store < $product->quantity) {
                throw new \Exception("Not enough stock for product: {$productModel->name}");
            }

            $productModel->decrement('product_store', $product->quantity);
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
        $row = $this->validatePaginationRow();
        return Order::where('due', '>', 0)->sortable()->paginate($row);
    }

    /**
     * Update the due payment for an order.
     */
    public function updateDue(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'due' => 'required|numeric|min:0',
        ])->validate();

        try {
            $order = Order::findOrFail($validatedData['order_id']);
            $new_due = max(0, $order->due - $validatedData['due']);
            $new_pay = $order->pay + $validatedData['due'];

            $order->update(['due' => $new_due, 'pay' => $new_pay]);

            return Redirect::route('order.pendingDue')->with('success', 'Due Amount Updated Successfully!');
        } catch (\Exception $e) {
            Log::error("Due payment update failed: {$e->getMessage()}");
            return Redirect::back()->with('error', 'Failed to update due payment.');
        }
    }

    /**
     * Download the invoice for an order.
     */
    public function invoiceDownload(int $order_id)
    {
        $order = Order::findOrFail($order_id);
        $orderDetails = OrderDetails::with('product')
            ->where('order_id', $order_id)
            ->orderByDesc('id')
            ->get();

        return view('orders.invoice-order', compact('order', 'orderDetails'));
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
}
