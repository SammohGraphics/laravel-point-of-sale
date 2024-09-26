<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Picqer\Barcode\BarcodeGeneratorHTML;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Support\Facades\Redirect;
use PDF;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $row = (int) request('row', 10);

        // Ensure row count is between 1 and 100
        if ($row < 1 || $row > 100) {
            abort(400, 'The per-page parameter must be an integer between 1 and 100.');
        }

        // Fetch products with relationships
        return view('products.index', [
            'products' => Product::with(['category', 'supplier'])
                ->filter(request(['search']))
                ->sortable()
                ->paginate($row)
                ->appends(request()->query()), // Maintain query parameters
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('products.create', [
            'categories' => Category::all(),
            'suppliers' => Supplier::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate and store the product
        $validatedData = $this->validateProduct($request);
        $validatedData['product_image'] = $this->handleImageUpload($request, 'product_image', 'public/products/');

        Product::create($validatedData);

        return Redirect::route('products.index')->with('success', 'Product has been created!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        // Generate Barcode
        $generator = new BarcodeGeneratorHTML();
        // $barcode = $generator->getBarcode($product->product_code, $generator::TYPE_CODE_128);

        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        return view('products.edit', [
            'categories' => Category::all(),
            'suppliers' => Supplier::all(),
            'product' => $product,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validatedData = $this->validateProduct($request, $product->id);
        $validatedData['product_image'] = $this->handleImageUpload($request, 'product_image', 'public/products/', $product->product_image);

        $product->update($validatedData);

        return Redirect::route('products.index')->with('success', 'Product has been updated!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // Ensure image exists before deletion
        if ($product->product_image) {
            Storage::delete('public/products/' . $product->product_image);
        }

        $product->delete();

        return Redirect::route('products.index')->with('success', 'Product has been deleted!');
    }

    /**
     * Show the form for importing products.
     */
    public function importView()
    {
        return view('products.import'); // Ensure this points to the correct Blade file for importing
    }

    /**
     * Handle file import.
     */
    public function importStore(Request $request)
    {
        // Validate the file input
        $request->validate(['upload_file' => 'required|file|mimes:xls,xlsx']);

        try {
            // Logic to handle import
            // ...
            return Redirect::route('products.index')->with('success', 'Data has been successfully imported!');
        } catch (Exception $e) {
            return Redirect::route('products.index')->with('error', 'There was a problem uploading the data!');
        }
    }

    /**
     * Export product data to PDF.
     */
    public function exportData()
    {
        $products = Product::all()->sortByDesc('product_id')->toArray();
        return $this->exportToPDF($products); // PDF Export only
    }

    /**
     * Validate product data.
     */
    protected function validateProduct(Request $request, $id = null)
    {
        $rules = [
            'product_image' => 'nullable|image|file|max:1024',
            'product_code' => 'required|string|unique:products,product_code,' . $id,
            'product_name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'product_garage' => 'nullable|string|max:255',
            'product_store' => 'nullable|string|max:255',
            'buying_date' => 'nullable|date_format:Y-m-d',
            'expire_date' => 'nullable|date_format:Y-m-d|after:buying_date',
            'buying_price' => 'required|numeric|min:0|regex:/^\d+(\.\d{1,2})?$/',
            'selling_price' => 'required|numeric|min:0|regex:/^\d+(\.\d{1,2})?$/',
        ];
        if ($id) {
            $rules['product_image'] = 'sometimes|image|file|max:1024'; // Allow image update but not required
        }

        return $request->validate($rules);
    }

    /**
     * Handle image upload and deletion.
     */
    protected function handleImageUpload(Request $request, $fieldName, $path, $oldFile = null)
    {
        if ($file = $request->file($fieldName)) {
            $fileName = hexdec(uniqid()) . '.' . $file->getClientOriginalExtension();
            $file->storeAs($path, $fileName);

            // Delete the old image if it exists
            if ($oldFile) {
                Storage::delete($path . $oldFile);
            }

            return $fileName;
        }

        return $oldFile;
    }

    /**
     * Export data to PDF.
     */
    public function exportToPDF($products)
    {
        try {
            $pdf = PDF::loadView('products.pdf', ['products' => $products]);
            return $pdf->download('ProductsList.pdf');
        } catch (Exception $e) {
            return Redirect::route('products.index')->with('error', 'There was a problem exporting the data to PDF!');
        }
    }


    /**
     * Add product to cart.
     */
    public function addCart(Request $request)
    {
        $product = Product::find($request->id);
        Cart::add([
            'id' => $product->id,
            'name' => $product->product_name,
            'qty' => 1, // Default quantity
            'price' => $product->selling_price,
            'options' => [
                // Other options if needed
            ]
        ]);

        return redirect()->back()->with('success', 'Product added to cart successfully!');
    }

    /**
     * Generate product code.
     */
    protected function generateProductCode()
    {
        return IdGenerator::generate([
            'table' => 'products',
            'field' => 'product_code',
            'length' => 4,
            'prefix' => 'PC',
        ]);
    }
}
