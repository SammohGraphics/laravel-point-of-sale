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
            <div>
                <h4 class="mb-3">Create Invoice</h4>
            </div>
        </div>

        <!-- Cart and Invoice Section -->
        <div class="col-lg-6 col-md-12 mb-3">
            <table class="table">
                <thead>
                    <tr class="ligth">
                        <th scope="col">Name</th>
                        <th scope="col">QTY</th>
                        <th scope="col">Price</th>
                        <th scope="col">SubTotal</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productItem as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td style="min-width: 140px;">
                                <form action="{{ route('invoice.updateCart', $item->rowId) }}" method="POST">
                                    @csrf
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="qty" required min="1" max="100" value="{{ old('qty', $item->qty) }}">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-success border-none" title="Submit"><i class="fas fa-check"></i></button>
                                        </div>
                                    </div>
                                </form>
                            </td>
                            <td>{{ $item->price }}</td>
                            <td>{{ $item->subtotal }}</td>
                            <td>
                                <a href="{{ route('invoice.deleteCart', $item->rowId) }}" class="btn btn-danger border-none" title="Delete"><i class="fa-solid fa-trash mr-0"></i></a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="row text-center">
                <div class="col-sm-6">
                    <p class="h4 text-primary">Quantity: {{ Cart::count() }}</p>
                </div>
                <div class="col-sm-6">
                    <p class="h4 text-primary">Subtotal: {{ Cart::subtotal() }}</p>
                </div>
                <div class="col-sm-6">
                    <p class="h4 text-primary">Vat: {{ Cart::tax() }}</p>
                </div>
                <div class="col-sm-6">
                    <p class="h4 text-primary">Total: {{ Cart::total() }}</p>
                </div>
            </div>

            <form action="{{ route('invoice.createInvoice') }}" method="POST">
                @csrf
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="input-group">
                            <select class="form-control @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id" required>
                                <option selected disabled>-- Select Customer --</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('customer_id')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                    <div class="col-md-12 mt-4">
                        <div class="d-flex justify-content-center">
                            <a href="{{ route('customers.create') }}" class="btn btn-primary mx-1">Add Customer</a>
                            <button type="submit" class="btn btn-success mx-1">Create Invoice</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Product Search Section -->
        <div class="col-lg-6 col-md-12">
            <div class="card card-block card-stretch card-height">
                <div class="card-body">
                    <form action="#" method="get" class="mb-3">
                        <div class="d-flex flex-wrap justify-content-between">
                            <div class="form-group row">
                                <label for="row" class="align-self-center mx-2">Row:</label>
                                <div>
                                    <select class="form-control" name="row">
                                        <option value="10" {{ request('row') == '10' ? 'selected' : '' }}>10</option>
                                        <option value="25" {{ request('row') == '25' ? 'selected' : '' }}>25</option>
                                        <option value="50" {{ request('row') == '50' ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ request('row') == '100' ? 'selected' : '' }}>100</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="control-label col-sm-3 align-self-center" for="search">Search:</label>
                                <div class="input-group col-sm-8">
                                    <input type="text" id="search" class="form-control" name="search" placeholder="Search product" value="{{ request('search') }}">
                                    <div class="input-group-append">
                                        <button type="submit" class="input-group-text bg-primary"><i class="fa-solid fa-magnifying-glass"></i></button>
                                        <a href="{{ route('products.index') }}" class="input-group-text bg-danger"><i class="fa-solid fa-trash"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive rounded mb-3">
                        <table class="table mb-0">
                            <thead class="bg-white text-uppercase">
                                <tr>
                                    <th>No.</th>
                                    <th>Photo</th>
                                    <th>@sortablelink('product_name', 'name')</th>
                                    <th>@sortablelink('selling_price', 'price')</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody class="ligth-body">
                                @forelse ($products as $product)
                                    <tr>
                                        <td>{{ (($products->currentPage() * $products->perPage()) - $products->perPage()) + $loop->iteration }}</td>
                                        <td>
                                            <img class="avatar-60 rounded" src="{{ $product->product_image ? asset('storage/products/'.$product->product_image) : asset('assets/images/product/default.webp') }}">
                                        </td>
                                        <td>{{ $product->product_name }}</td>
                                        <td>{{ $product->selling_price }}</td>
                                        <td>
                                            <form action="{{ route('invoice.addCart') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $product->id }}">
                                                <input type="hidden" name="name" value="{{ $product->product_name }}">
                                                <input type="hidden" name="price" value="{{ $product->selling_price }}">
                                                <button type="submit" class="btn btn-primary" title="Add"><i class="far fa-plus"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-danger">No Products Found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function(){
    $('#search').on('keyup', function() {
        let query = $(this).val();

        $.ajax({
            url: "{{ route('invoice.liveSearch') }}",
            type: 'GET',
            data: { search: query },
            success: function(data) {
                let tableRow = '';
                if (data.length > 0) {
                    $.each(data, function(index, product) {
                        tableRow += '<tr>';
                        tableRow += '<td>' + (index + 1) + '</td>';
                        tableRow += '<td><img class="avatar-60 rounded" src="' + (product.product_image ? '/storage/products/' + product.product_image : '/assets/images/product/default.webp') + '" /></td>';
                        tableRow += '<td>' + product.product_name + '</td>';
                        tableRow += '<td>' + product.selling_price + '</td>';
                        tableRow += '<td><form action="{{ route('invoice.addCart') }}" method="POST">@csrf<input type="hidden" name="id" value="' + product.id + '"><input type="hidden" name="name" value="' + product.product_name + '"><input type="hidden" name="price" value="' + product.selling_price + '"><button type="submit" class="btn btn-primary"><i class="far fa-plus"></i></button></form></td>';
                        tableRow += '</tr>';
                    });
                } else {
                    tableRow = '<tr><td colspan="5" class="text-center text-danger">No Products Found</td></tr>';
                }
                $('tbody').html(tableRow);
            },
            error: function(error) {
                console.log(error);
            }
        });
    });
});
</script>
@endpush
@endsection
