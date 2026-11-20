<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Support\ActivityLogger;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('name')->paginate(10);
        return view('categories.index', compact('categories'));
    }
    
    public function create()
    {
        return view('categories.create');
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required','string','max:100','unique:categories,name'],
            'description' => ['nullable','string','max:1000'],
            'status' => ['required', Rule::in(['Aktif', 'Tidak Aktif'])],
        ]);
        
        $category = Category::create($validated);
        ActivityLogger::record('category.created', "Menambahkan kategori: {$category->name}.");
        return redirect()->route('categories.index')->with('success','Kategori berhasil ditambahkan.');
    }
    
    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }
    
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => ['required','string','max:100',Rule::unique('categories','name')->ignore($category->id)],
            'description' => ['nullable','string','max:1000'],
            'status' => ['required', Rule::in(['Aktif', 'Tidak Aktif'])], // <-- Tambahan validasi status
        ]);
        
        $category->update($validated);
        ActivityLogger::record('category.updated', "Memperbarui kategori: {$category->name}.");
        return redirect()->route('categories.index')->with('success','Kategori berhasil diperbarui.');
    }
    
    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->with('error','Kategori masih digunakan oleh produk.');
        }
    
        $categoryName = $category->name;
        $category->delete();
        ActivityLogger::record('category.deleted', "Menghapus kategori: {$categoryName}.");
        return redirect()->route('categories.index')->with('success','Kategori berhasil dihapus.');
    }
}