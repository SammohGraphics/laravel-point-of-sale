<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        // Total Paid and Due Amount (formatted with commas and two decimal places)
        $total_paid = number_format(Order::sum('pay'), 2, '.', ',');
        $total_due = number_format(Order::sum('due'), 2, '.', ',');

        // Count complete, pending, and cancelled orders
        $complete_orders_count = Order::where('order_status', 'complete')->count();
        $pending_orders_count = Order::where('order_status', 'pending')->count();
        $cancelled_orders_count = Order::where('order_status', 'cancelled')->count();

        // Fetch top products
        $products = Product::orderBy('product_store')->limit(5)->get();
        $new_products = Product::orderBy('buying_date')->limit(2)->get();

        // Fetch daily sales data for the graph
        $daily_sales = Order::selectRaw('DATE(created_at) as date, SUM(pay) as total')
                            ->groupBy('date')
                            ->orderBy('date', 'desc')
                            ->take(7) // Last 7 days of sales
                            ->get();

        // Time-based greeting
        $currentHour = Carbon::now()->format('H');
        $greeting = $this->getGreeting($currentHour);

        // Fetch completed orders
        $completed_orders = Order::where('order_status', 'complete')->get();

        // Pass all the variables to the view in a single return statement
        return view('dashboard.index', [
            'completed_orders' => $completed_orders,
            'total_paid' => $total_paid,
            'total_due' => $total_due,
            'complete_orders_count' => $complete_orders_count,
            'pending_orders_count' => $pending_orders_count,
            'cancelled_orders_count' => $cancelled_orders_count,
            'products' => $products,
            'new_products' => $new_products,
            'daily_sales' => $daily_sales,
            'greeting' => $greeting // Ensure greeting is passed to the view
        ]);
    }

    protected function getGreeting($hour)
    {
        if ($hour >= 5 && $hour < 11) {
            return 'Good Morning';
        } elseif ($hour >= 12 && $hour < 15) {
            return 'Good Afternoon';
        } elseif ($hour >= 15 && $hour < 19) {
            return 'Good Evening';
        } else {
            return 'Good Night';
        }
    }
}
