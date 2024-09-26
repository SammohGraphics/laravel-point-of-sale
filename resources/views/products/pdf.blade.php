<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exported Products</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        img {
            width: 80px; /* Set a fixed size for images */
            height: 80px;
            object-fit: cover; /* Ensure images maintain aspect ratio */
        }
        .no-image {
            font-style: italic;
            color: #888;
        }
    </style>
</head>
<body>
    <h1>Products List</h1>
    <table>
        <thead>
            <tr>
                <th>S/N</th> <!-- Add Serial Number Column -->
                <th>PRODUCT CODE</th>
                <th>PRODUCT IMAGE</th>
                <th>PRODUCT NAME</th>
                {{-- <th>CATEGORY</th> --}}
                <th>SUPPLIER</th>
                <th>UNIT</th>
                <th>PRICE</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $index => $product) <!-- Use $index for Serial Number -->
                <tr>
                    <td>{{ $index + 1 }}</td> <!-- Display Serial Number -->
                    <td>{{ $product['product_code'] }}</td>
                    <td>
                        @if($product['product_image'])
                            <img src="{{ asset('assets/images/products/' . $product['product_image']) }}" alt="Product Image">
                        @else
                            <span class="no-image">No image</span> <!-- Display placeholder for missing images -->
                        @endif
                    </td>
                    <td>{{ $product['product_name'] }}</td>
                    {{-- <td>{{ $product['category']['name'] }}</td> --}}
                    <td>{{ $product['supplier']['name'] }}</td>
                    <td>{{ $product['product_garage'] ?? 'N/A' }}</td> <!-- Handle null values gracefully -->
                    <td>{{ number_format($product['selling_price'], 2) }}</td> <!-- Price formatted with commas and decimals -->
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
