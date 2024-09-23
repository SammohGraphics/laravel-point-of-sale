@extends('dashboard.body.main')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            @if (session()->has('success'))
                <div class="alert text-white bg-success" role="alert">
                    <div class="iq-alert-text">{{ session('success') }}</div>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <i class="ri-close-line"></i>
                    </button>
                </div>
            @endif
        </div>
        <div class="col-lg-4">
            <div class="card card-transparent card-block card-stretch card-height border-none">
                <div class="card-body p-0 mt-lg-2 mt-0">
                    <h3 class="mb-3">Hi {{ auth()->user()->name }}, {{ $greeting }}</h3>
                    <p class="mb-0 mr-4">Your dashboard gives you views of key performance or business process.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="row">
                <div class="col-lg-4 col-md-4">
                    <div class="card card-block card-stretch card-height">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-4 card-total-sale">
                                <div class="icon iq-icon-box-2 bg-info-light">
                                    <img src="../assets/images/product/1.png" class="img-fluid" alt="image">
                                </div>
                                <div>
                                    <p class="mb-2">Total Paid</p>
                                    <h4>Tsh {{ $total_paid }}</h4>
                                </div>
                            </div>
                            <div class="iq-progress-bar mt-2">
                                <span class="bg-info iq-progress progress-1" data-percent="85"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4">
                    <div class="card card-block card-stretch card-height">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-4 card-total-sale">
                                <div class="icon iq-icon-box-2 bg-danger-light">
                                    <img src="../assets/images/product/2.png" class="img-fluid" alt="image">
                                </div>
                                <div>
                                    <p class="mb-2">Total Due</p>
                                    <h4>Tsh {{ $total_due }}</h4>
                                </div>
                            </div>
                            <div class="iq-progress-bar mt-2">
                                <span class="bg-danger iq-progress progress-1" data-percent="70"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4">
                    <div class="card card-block card-stretch card-height">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-4 card-total-sale">
                                <div class="icon iq-icon-box-2 bg-success-light">
                                    <img src="../assets/images/product/3.png" class="img-fluid" alt="image">
                                </div>
                                <div>
                                    <p class="mb-2">Complete Orders</p>
                                    <h4>{{ $complete_orders_count }}</h4>
                                </div>
                            </div>
                            <div class="iq-progress-bar mt-2">
                                <span class="bg-success iq-progress progress-1" data-percent="75"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <!-- Pie Chart for Order Status -->
            <div class="col-lg-4 d-flex justify-content-center">
                <div class="card card-block card-stretch card-height">
                    <div class="card-body">
                        <h5 class="card-title text-center">Order Status</h5>
                        <canvas id="orderStatusChart"></canvas>
                    </div>
                </div>
            </div>
            <!-- Line Chart for Daily Sales -->
            <div class="col-lg-4 d-flex justify-content-center">
                <div class="card card-block card-stretch card-height">
                    <div class="card-body">
                        <h5 class="card-title text-center">Daily Sales</h5>
                        <canvas id="dailySalesChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Completed Orders List -->
            <div class="col-lg-4 d-flex justify-content-center">
                <div class="card card-block card-stretch card-height">
                    <div class="card-body">
                        <h5 class="card-title text-center">Completed Orders</h5>
                        @foreach($completed_orders as $order)
                            <div class="order-item d-flex align-items-center mb-3">
                                <div class="order-image">
                                    <img src="{{ $order->product_image }}" class="img-fluid" alt="product image" style="width: 50px; height: 50px;">
                                </div>
                                <div class="order-details ml-3">
                                    <p class="mb-0"><strong>{{ $order->product_name }}</strong></p>
                                    <p class="mb-0">Tsh {{ number_format($order->pay, 2) }}</p>
                                </div>
                            </div>
                            <hr>
                        @endforeach
                    </div>
                </div>
            </div>


        </div>
    </div>
</div>
@endsection

@section('specificpagescripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Pie Chart for Order Status
    var ctxPie = document.getElementById('orderStatusChart').getContext('2d');
    var orderStatusChart = new Chart(ctxPie, {
        type: 'pie',
        data: {
            labels: ['Complete', 'Pending', 'Cancelled'],
            datasets: [{
                data: [{{ $complete_orders_count }}, {{ $pending_orders_count }}, {{ $cancelled_orders_count }}],
                backgroundColor: ['#78C091', '#32BDEA', '#E08DB4'],
                hoverBackgroundColor: ['#78C091', '#32BDEA', '#E08DB4'],
                borderColor: '#fff',
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            size: 14
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleFont: {
                        size: 16,
                    },
                    bodyFont: {
                        size: 14
                    }
                }
            }
        }
    });

    // Line Chart for Daily Sales
    var ctxLine = document.getElementById('dailySalesChart').getContext('2d');
    var dailySalesChart = new Chart(ctxLine, {
        type: 'line',
        data: {
            labels: {!! json_encode($daily_sales->pluck('date')) !!}, // Dates
            datasets: [{
                label: 'Sales in TSH',
                data: {!! json_encode($daily_sales->pluck('total')) !!}, // Sales Data
                backgroundColor: 'rgba(0, 123, 255, 0.5)',
                borderColor: '#32BDEA',
                fill: true,
                tension: 0.1,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointHoverBackgroundColor: '#32BDEA'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleFont: {
                        size: 16,
                    },
                    bodyFont: {
                        size: 14
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 5000
                    }
                }
            }
        }
    });
</script>
@endsection
