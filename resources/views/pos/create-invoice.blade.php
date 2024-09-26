@extends('dashboard.body.main')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-block">
                <div class="card-header d-flex justify-content-between bg-primary">
                    <div class="iq-header-title">
                        <h4 class="card-title mb-0">Invoice</h4>
                    </div>

                    <div class="invoice-btn d-flex">
                        <form action="{{ route('pos.printInvoice') }}" method="post">
                            @csrf
                            <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                            <button type="submit" class="btn btn-primary-dark mr-2">
                                <i class="las la-print"></i> Print
                            </button>
                        </form>

                        <button type="button" class="btn btn-primary-dark mr-2" data-toggle="modal" data-target="#createInvoiceModal">
                            Create
                        </button>

                        <!-- Modal -->
                        <div class="modal fade" id="createInvoiceModal" tabindex="-1" role="dialog" aria-labelledby="createInvoiceModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header bg-white">
                                        <h3 class="modal-title text-center mx-auto">Invoice of {{ $customer->name }}<br/>Total Amount Tsh {{ Cart::total() }}</h3>
                                    </div>
                                    <form action="{{ route('pos.storeOrder') }}" method="post" id="invoiceForm">
                                        @csrf
                                        <div class="modal-body">
                                            <input type="hidden" name="customer_id" value="{{ $customer->id }}">

                                            <!-- Payment Status -->
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="payment_status">Payment</label>
                                                    <select class="form-control @error('payment_status') is-invalid @enderror" name="payment_status" id="payment_status" required>
                                                        <option selected="" disabled="">-- Select Payment --</option>
                                                        <option value="HandCash" {{ old('payment_status') == 'HandCash' ? 'selected' : '' }}>HandCash</option>
                                                        <option value="Cheque" {{ old('payment_status') == 'Cheque' ? 'selected' : '' }}>Cheque</option>
                                                        <option value="Due" {{ old('payment_status') == 'Due' ? 'selected' : '' }}>Due</option>
                                                    </select>
                                                    @error('payment_status')
                                                        <div class="invalid-feedback">
                                                            {{ $message }}
                                                        </div>
                                                    @enderror
                                                </div>
                                            </div>


                                            <!-- Pay Now -->
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="pay">Pay Now</label>
                                                    <input type="number" class="form-control @error('pay') is-invalid @enderror" id="pay" name="pay" value="{{ old('pay') }}" required>
                                                    @error('pay')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Save</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="row">
                        <div class="col-sm-12">
                            <img src="{{ asset('assets/images/logo.png') }}" class="logo-invoice img-fluid mb-3">
                            <h5 class="mb-3">Hello, {{ $customer->name }}</h5>
                        </div>
                    </div>

                    <!-- Invoice Details -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="table-responsive-sm">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th scope="col">Order Date</th>
                                            <th scope="col">Order Status</th>
                                            <th scope="col">Billing Address</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>{{ now()->format('M d, Y') }}</td>
                                            <td><span class="badge badge-danger">Unpaid</span></td>
                                            <td>
                                                <p class="mb-0">{{ $customer->address }}<br>
                                                    Shop Name: {{ $customer->shopname ?: '-' }}<br>
                                                    Phone: {{ $customer->phone }}<br>
                                                    Email: {{ $customer->email }}<br>
                                                </p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="row">
                        <div class="col-sm-12">
                            <h5 class="mb-3">Order Summary</h5>
                            <div class="table-responsive-lg">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th class="text-center" scope="col">#</th>
                                            <th scope="col">Item</th>
                                            <th class="text-center" scope="col">Quantity</th>
                                            <th class="text-center" scope="col">Price</th>
                                            <th class="text-center" scope="col">Totals</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($content as $item)
                                        <tr>
                                            <th class="text-center" scope="row">{{ $loop->iteration }}</th>
                                            <td>
                                                <h6 class="mb-0">{{ $item->name }}</h6>
                                            </td>
                                            <td class="text-center">{{ $item->qty }}</td>
                                            <td class="text-center">{{ $item->price }}</td>
                                            <td class="text-center"><b>{{ $item->subtotal }}</b></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Order Details -->
                    <div class="row mt-4 mb-3">
                        <div class="offset-lg-8 col-lg-4">
                            <div class="or-detail rounded">
                                <div class="p-3">
                                    <h5 class="mb-3">Order Details</h5>
                                    <div class="mb-2">
                                        <h6>Sub Total</h6>
                                        <p>Tsh {{ Cart::subtotal() }}</p>
                                    </div>
                                    <div>
                                        <h6>Vat (18%)</h6>
                                        <p>Tsh {{ Cart::tax() }}</p>
                                    </div>
                                </div>
                                <div class="ttl-amt py-2 px-3 d-flex justify-content-between align-items-center">
                                    <h6>Total</h6>
                                    <h3 class="text-primary font-weight-700">Tsh {{ Cart::total() }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Client-Side Validation -->
<script>
    document.getElementById('invoiceForm').addEventListener('submit', function (e) {
        const paymentStatus = document.getElementById('payment_status');
        const payField = document.getElementById('pay');

        if (!paymentStatus.value) {
            alert('Please select a payment method.');
            e.preventDefault();
        }

        if (!payField.value || isNaN(payField.value) || parseFloat(payField.value) <= 0) {
            alert('Please enter a valid amount to pay.');
            e.preventDefault();
        }
    });

    document.getElementById('invoiceForm').addEventListener('submit', function (e) {
        const paymentStatus = document.getElementById('payment_status');
        const payField = document.getElementById('pay');

        if (!paymentStatus.value) {
            alert('Please select a payment method.');
            e.preventDefault(); // Prevent form submission if validation fails
        }

        if (!payField.value || isNaN(payField.value) || parseFloat(payField.value) <= 0) {
            alert('Please enter a valid amount to pay.');
            e.preventDefault(); // Prevent form submission if validation fails
        }
    });
</script>

@endsection
