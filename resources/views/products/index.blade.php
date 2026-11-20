@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Data Produk</h3>
    <div>
        <a href="{{ route('products.export', request()->query()) }}" class="btn btn-outline-success">Ekspor CSV</a>
        @if(auth()->check() && auth()->user()->role === 'admin')
            <a href="{{ route('products.create') }}" class="btn btn-primary">Tambah Produk</a>
        @endif
    </div>
</div>

<form method="GET" class="row g-2 my-3">
    <div class="col-md-6 col-lg-3">
        <input name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari kode, nama, merek">
    </div>
    <div class="col-md-6 col-lg-2">
        <select name="category_id" class="form-select">
            <option value="">Semua kategori</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 col-lg-2">
        <select name="stock_status" class="form-select">
            <option value="">Semua kondisi stok</option>
            <option value="low" @selected(request('stock_status') === 'low')>Stok menipis (1-5)</option>
            <option value="out" @selected(request('stock_status') === 'out')>Stok habis (0)</option>
        </select>
    </div>
    <div class="col-md-6 col-lg-2">
        <select name="sort_by" class="form-select">
            <option value="created_at" @selected(request('sort_by', 'created_at') === 'created_at')>Terbaru</option>
            <option value="name" @selected(request('sort_by') === 'name')>Nama</option>
            <option value="price" @selected(request('sort_by') === 'price')>Harga</option>
            <option value="stock" @selected(request('sort_by') === 'stock')>Stok</option>
        </select>
    </div>
    <div class="col-md-6 col-lg-1">
        <select name="sort_direction" class="form-select" aria-label="Urutan">
            <option value="asc" @selected(request('sort_direction') === 'asc')>Naik</option>
            <option value="desc" @selected(request('sort_direction', 'desc') === 'desc')>Turun</option>
        </select>
    </div>
    <div class="col-md-6 col-lg-2">
        <button class="btn btn-outline-primary">Cari / Filter</button>
    </div>
</form>

<div class="card">
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Gambar</th>
                    <th>Kode</th>
                    <th>Produk</th>
                    <th>Kategori</th>
                    <th>Merek</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($products as $product)
                <tr>
                    <td>
                        <img src="{{ $product->image ? \Illuminate\Support\Facades\Storage::disk('public')->url($product->image) : asset('img/product-placeholder.svg') }}"
                             alt="Gambar {{ $product->name }}"
                             loading="lazy"
                             style="width: 64px; height: 64px; object-fit: cover; border-radius: 6px;">
                    </td>
                    <td>{{ $product->product_code }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->category->name ?? '-' }}</td>
                    <td>{{ $product->brand }}</td>
                    <td>Rp {{ number_format((float)$product->price, 0, ',', '.') }}</td>
                    <td>
                        @if($product->stock <= 0)
                            <span class="badge badge-danger">Habis</span>
                        @elseif($product->stock <= \App\Models\Product::LOW_STOCK_THRESHOLD)
                            <span class="badge badge-warning">Menipis</span>
                        @else
                            <span class="badge badge-success">Tersedia</span>
                        @endif
                        <div>{{ $product->stock }} unit</div>
                    </td>
                    <td>
                        {{-- Tombol Edit & Hapus Hanya Muncul untuk Admin --}}
                        @if(auth()->check() && auth()->user()->role === 'admin')
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-warning">Edit</a>
                            
                            <form class="d-inline" method="POST" action="{{ route('products.destroy', $product) }}">
                                @csrf 
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus produk ini?')">Hapus</button>
                            </form>
                        @else
                            <span class="text-muted small">Hanya Lihat</span>
                        @endif
                    </td>
                </tr>
            @empty 
                <tr>
                    <td colspan="8" class="text-center">Belum ada produk.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
        
        {{ $products->links() }}
    </div>
</div>
@endsection