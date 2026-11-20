@extends('layouts.app')
@section('content')
<h3>Tambah Produk</h3>
<form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
@csrf
@include('products._form')
<button class="btn btn-primary mt-3">Simpan Produk</button>
<a href="{{ route('products.index') }}" class="btn btn-secondary mt-3">Batal</a>
</form>
@endsection