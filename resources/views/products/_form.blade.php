<div class="row g-3">
<div class="col-md-6">
<label>Kode produk</label>
<input name="product_code" class="form-control" value="{{ old('product_code',$product->product_code ?? '') }}">
@error('product_code')<small class="text-danger">{{ $message }}</small>@enderror
</div>
<div class="col-md-6">
<label>Nama produk</label>
<input name="name" class="form-control" value="{{ old('name',$product->name ?? '') }}">
@error('name')<small class="text-danger">{{ $message }}</small>@enderror
</div>
<div class="col-md-6">
<label>Kategori</label>
<select name="category_id" class="form-select">
    <option value="">Pilih kategori</option>
@foreach($categories as $category)
<option value="{{ $category->id }}" @selected(old('category_id',$product->category_id ?? '') == $category->id)>{{ $category->name
}}</option>
@endforeach
</select>
@error('category_id')<small class="text-danger">{{ $message }}</small>@enderror
</div>
<div class="col-md-6">
<label>Merek</label>
<input name="brand" class="form-control" value="{{ old('brand',$product->brand ?? '') }}">
</div>
<div class="col-md-6">
<label>Harga</label>
<input type="number" min="0" step="0.01" name="price" class="form-control" value="{{ old('price',$product->price ?? '') }}">
</div>
<div class="col-md-6">
<label>Stok</label>
<input type="number" min="0" name="stock" class="form-control" value="{{ old('stock',$product->stock ?? 0) }}">
</div>
<div class="col-12">
<label>Deskripsi</label>
<textarea name="description" class="form-control">{{ old('description',$product->description ?? '') }}</textarea>
</div>
<div class="col-12">
<label for="image" class="form-label">Gambar produk{{ isset($product) ? ' (opsional)' : '' }}</label>
<input type="file" id="image" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp" {{ isset($product) ? '' : 'required' }}>
<small class="form-text text-muted">Format JPG, PNG, atau WEBP. Maksimal 5 MB.</small>
@error('image')<small class="d-block text-danger">{{ $message }}</small>@enderror
<div class="mt-3">
    <img id="image-preview"
         src="{{ isset($product) && $product->image ? \Illuminate\Support\Facades\Storage::disk('public')->url($product->image) : asset('img/product-placeholder.svg') }}"
         alt="Preview gambar produk"
         style="width: 160px; height: 120px; object-fit: cover; border-radius: 8px;">
</div>
</div>
</div>

@push('scripts')
<script>
    document.getElementById('image').addEventListener('change', function (event) {
        const file = event.target.files[0];
        if (file) {
            document.getElementById('image-preview').src = URL.createObjectURL(file);
        }
    });
</script>
@endpush