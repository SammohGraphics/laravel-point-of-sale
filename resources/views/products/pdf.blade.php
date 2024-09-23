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
    </style>
</head>
<body>
    <h1>Products List</h1>
    <table>
        <thead>
            <tr>
                <th>Product Code</th>
                <th>Product Name</th>
                <th>Category</th>
                <th>Supplier</th>
                <th>Buying Price</th>
                <th>Selling Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
                <tr>
                    <td>{{ $product['product_code'] }}</td>
                    <td>{{ $product['product_name'] }}</td>
                    <td>{{ $product['category']['name'] }}</td>
                    <td>{{ $product['supplier']['name'] }}</td>
                    <td>{{ $product['buying_price'] }}</td>
                    <td>{{ $product['selling_price'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
