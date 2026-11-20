@extends('layouts.app')
@section('title','Kategori Produk')
@section('content')
<div class="d-flex justify-content-between mb-3">
 <h3>Kategori Produk</h3>
 @if(auth()->user()->role === 'admin')
     <a class="btn btn-primary" href="{{ route('categories.create') }}">Tambah Kategori</a>
 @endif
</div>
<div class="card"><div class="card-body">
<table class="table">
<thead>
    <tr>
        <th>Nama</th>
        <th>Deskripsi</th>
        <th>Status</th> 
        <th>Jumlah Produk</th>
        <th>Aksi</th>
    </tr>
</thead>
<tbody>
@forelse($categories as $category)
<tr>
    <td>{{ $category->name }}</td>
    <td>{{ $category->description }}</td>
    <td>
        <span class="badge {{ $category->status == 'Aktif' ? 'bg-success' : 'bg-secondary' }}">
            {{ $category->status }}
        </span>
    </td>
    <td>{{ $category->products_count }}</td>
    <td>
    @if(auth()->user()->role === 'admin')
        <a class="btn btn-sm btn-warning" href="{{ route('categories.edit',$category) }}">Edit</a>
        <form class="d-inline" method="POST" action="{{ route('categories.destroy',$category) }}">
        @csrf @method('DELETE')
        <button class="btn btn-sm btn-danger" onclick="return confirm('Hapus kategori?')">Hapus</button>
        </form>
    @else
        <span class="text-muted small">Hanya Lihat</span>
    @endif
    </td>
</tr>
@empty
<tr><td colspan="5" class="text-center">Belum ada kategori.</td></tr> 
@endforelse
</tbody></table>{{ $categories->links() }}
</div></div>
@endsection