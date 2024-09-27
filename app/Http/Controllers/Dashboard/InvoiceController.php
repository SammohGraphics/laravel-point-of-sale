<?php
namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function createOrder(Request $request)
    {
        $cashier_name = $request->input('cashier_name');
        $order_date = now()->toDateString();
        $time_order = now()->toTimeString();
        $grandtotal = floatval($request->input('grandtotal'));
        $cash = floatval($request->input('cash'));
        $change = $cash - $grandtotal;

        if ($cash < $grandtotal) {
            return redirect()->back()->with('error', 'Insufficient cash! Please enter a sufficient amount.');
        }

        DB::beginTransaction();

        try {
            // Insert into invoices table
            $invoice = Invoice::create([
                'cashier_name' => $cashier_name,
                'order_date' => $order_date,
                'time_order' => $time_order,
                'total' => $grandtotal,
                'paid' => $cash,
                'due' => $change,
            ]);

            // Process each product in the order
            foreach ($request->input('products') as $product) {
                $product_id = $product['id'];
                $qty = intval($product['qty']);
                $price = floatval($product['price']);
                $total = $price * $qty;

                // Insert into invoice_details table
                InvoiceDetail::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $product_id,
                    'product_code' => $product['code'],
                    'product_name' => $product['name'],
                    'qty' => $qty,
                    'price' => $price,
                    'total' => $total,
                    'order_date' => $order_date,
                ]);

                // Update product stock
                $productModel = Product::find($product_id);
                $productModel->stock -= $qty;
                $productModel->save();
            }

            DB::commit();
            return redirect()->route('invoices.index')->with('success', 'Transaction Successful!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Transaction Failed! Error: ' . $e->getMessage());
        }
    }
}
?>