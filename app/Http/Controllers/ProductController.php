<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Support\ActivityLogger;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = $this->filteredProducts($request)->paginate(10)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function export(Request $request)
    {
        $products = $this->filteredProducts($request);

        return response()->streamDownload(function () use ($products) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, [
                'Kode Produk',
                'Nama Produk',
                'Kategori',
                'Merek',
                'Harga',
                'Stok',
                'Status Stok',
                'Deskripsi',
            ], ',', '"', '\\');

            $products->chunk(500, function ($productChunk) use ($output) {
                foreach ($productChunk as $product) {
                    $stockStatus = match (true) {
                        $product->stock <= 0 => 'Habis',
                        $product->stock <= Product::LOW_STOCK_THRESHOLD => 'Menipis',
                        default => 'Tersedia',
                    };

                    fputcsv($output, [
                        $this->safeCsvValue($product->product_code),
                        $this->safeCsvValue($product->name),
                        $this->safeCsvValue($product->category?->name),
                        $this->safeCsvValue($product->brand),
                        $product->price,
                        $product->stock,
                        $stockStatus,
                        $this->safeCsvValue($product->description),
                    ], ',', '"', '\\');
                }
            });

            fclose($output);
        }, 'produk-' . now()->format('Ymd-His') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function filteredProducts(Request $request): Builder
    {
        $filters = $request->validate([
            'stock_status' => ['nullable', Rule::in(['low', 'out'])],
            'sort_by' => ['nullable', Rule::in(['name', 'price', 'stock', 'created_at'])],
            'sort_direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        return Product::with('category')
            ->when($request->filled('q'), function ($query) use ($request) {
                $keyword = $request->string('q')->toString();
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                        ->orWhere('product_code', 'like', "%{$keyword}%")
                        ->orWhere('brand', 'like', "%{$keyword}%");
                });
            })
            ->when($request->filled('category_id'),
                fn ($query) => $query->where('category_id', $request->category_id))
            ->when(($filters['stock_status'] ?? null) === 'low',
                fn ($query) => $query->whereBetween('stock', [1, Product::LOW_STOCK_THRESHOLD]))
            ->when(($filters['stock_status'] ?? null) === 'out',
                fn ($query) => $query->where('stock', 0))
            ->orderBy($sortBy, $sortDirection)
            ->orderBy('id', $sortDirection);
    }

    private function safeCsvValue(?string $value): string
    {
        $value ??= '';

        return preg_match('/^[\x00-\x20]*[=+\-@]/', $value) === 1
            ? "'" . $value
            : $value;
    }

    public function create()
    {
    $categories = Category::orderBy('name')->get();
    return view('products.create', compact('categories'));
    }
    public function store(Request $request)
    {
    $validated = $request->validate([
    'category_id' => ['required','exists:categories,id'],
    'product_code' => ['required','string','max:50','unique:products,product_code'],
    'name' => ['required','string','max:150'],
    'brand' => ['nullable','string','max:100'],
    'price' => ['required','numeric','min:0'],
    'stock' => ['required','integer','min:0'],
    'description' => ['nullable','string','max:2000'],
    'image' => ['required','image','mimes:jpeg,png,jpg,webp','max:5120'],
    ]);

    $imagePath = $request->file('image')->store('products', 'public');
    if (! $imagePath) {
        throw new \RuntimeException('Gagal menyimpan gambar produk.');
    }

    $validated['image'] = $imagePath;
    $product = Product::create($validated);
    ActivityLogger::record(
        'product.created',
        "Menambahkan produk: {$product->name} ({$product->product_code})."
    );
    return redirect()->route('products.index')->with('success','Produk berhasil ditambahkan.');
    }
    public function edit(Product $product)
    {
    $categories = Category::orderBy('name')->get();
    return view('products.edit', compact('product','categories'));
    }
    public function update(Request $request, Product $product)
    {
    $validated = $request->validate([
    'category_id' => ['required','exists:categories,id'],
    'product_code' => ['required','string','max:50',Rule::unique('products','product_code')->ignore($product->id)],
    'name' => ['required','string','max:150'],
    'brand' => ['nullable','string','max:100'],
    'price' => ['required','numeric','min:0'],
    'stock' => ['required','integer','min:0'],
    'description' => ['nullable','string','max:2000'],
    'image' => ['nullable','image','mimes:jpeg,png,jpg,webp','max:5120'],
    ]);

    $oldImage = $product->image;
    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('products', 'public');
        if (! $imagePath) {
            throw new \RuntimeException('Gagal menyimpan gambar produk.');
        }

        $validated['image'] = $imagePath;
    } else {
        unset($validated['image']);
    }

    $product->update($validated);
    ActivityLogger::record(
        'product.updated',
        "Memperbarui produk: {$product->name} ({$product->product_code})."
    );
    if (isset($validated['image']) && $oldImage) {
        Storage::disk('public')->delete($oldImage);
    }

    return redirect()->route('products.index')->with('success','Produk berhasil diperbarui.');
    }
    public function destroy(Product $product)
    {
    $image = $product->image;
    $description = "Menghapus produk: {$product->name} ({$product->product_code}).";
    $product->delete();
    ActivityLogger::record('product.deleted', $description);
    if ($image) {
        Storage::disk('public')->delete($image);
    }

    return redirect()->route('products.index')->with('success','Produk berhasil dihapus.');
    }
    }
      
