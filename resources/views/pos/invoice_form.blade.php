@extends('dashboard.body.main')

@section('container')
<div class="container invoice-container">
    <h1>Transactions</h1>
    <form method="POST" action="{{ route('saveOrder') }}">
        @csrf
        <!-- Cashier and Date Details -->
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Cashier:</label>
                    <input type="text" class="form-control" name="cashier_name" value="{{ auth()->user()->name }}" readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Date:</label>
                    <input type="text" class="form-control" value="{{ now()->format('d-m-Y') }}" readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Time:</label>
                    <input type="text" class="form-control" value="{{ now()->format('H:i') }}" readonly>
                </div>
            </div>
        </div>

        <!-- Products Section -->
        <div class="row">
            <div class="col-md-8">
                <div class="form-group">
                    <label>Search Item or Scan Barcode:</label>
                    <input type="text" class="form-control" id="barcode" placeholder="Enter barcode or product name">
                </div>
            </div>
        </div>

        <!-- Product Table -->
        <div class="table-wrapper">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product Code</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Dynamically add product rows here -->
                </tbody>
            </table>
        </div>

        <!-- Payment Section -->
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="grandtotal">Grand Total:</label>
                    <input type="text" class="form-control" name="grandtotal" id="grandtotal" readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="cash">Cash:</label>
                    <input type="text" class="form-control" name="cash" id="cash">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="change">Change:</label>
                    <input type="text" class="form-control" name="change" id="change" readonly>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary">Save Order</button>
            </div>
        </div>
    </form>
</div>
@endsection
