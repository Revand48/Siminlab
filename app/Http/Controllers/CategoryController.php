<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // Variabel untuk data umum
    protected $title = 'Category';
    protected $menu = 'category';
    protected $directory = 'admin.category'; // Folder view category

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Menyiapkan array untuk dikirim ke view
        $data['title'] = $this->title;
        $data['menu'] = $this->menu;

        // Mengambil data dari database
        $data['categories'] = Category::latest()->get();

        // Me-return view beserta data
        return view($this->directory . '.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Menyiapkan array untuk dikirim ke view
        $data['title'] = $this->title;
        $data['menu'] = $this->menu;

        // Me-return view form create
        return view($this->directory . '.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validasi data
        $validatedData = $request->validate([
            'name' => 'required|max:255|unique:categories',
        ]);

        // 2. Simpan data ke database
        $category = Category::create($validatedData);

        // 3. Redirect dengan pesan sukses atau gagal
        if ($category) {
            return redirect()->route('category.index')->with([
                'status' => 'success',
                'title' => 'Berhasil',
                'message' => 'Data Berhasil Ditambahkan!'
            ]);
        } else {
            return redirect()->route('category.index')->with([
                'status' => 'danger',
                'title' => 'Gagal',
                'message' => 'Data Gagal Ditambahkan!'
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        // Menyiapkan data umum
        $data['title'] = $this->title;
        $data['menu'] = $this->menu;

        // Mencari data category berdasarkan ID (model binding)
        $data['category'] = $category;

        // Me-return view beserta data
        return view($this->directory . '.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        // 1. Validasi data
        $validatedData = $request->validate([
            'name' => 'required|max:255|unique:categories,name,' . $category->id,
        ]);

        // 2. Update data di database
        $updateProcess = $category->update($validatedData);

        // 3. Redirect dengan pesan sukses
        if ($updateProcess) {
            return redirect()->route('category.index')->with([
                'status' => 'success',
                'title' => 'Berhasil',
                'message' => 'Data Berhasil Diubah!'
            ]);
        } else {
            return redirect()->route('category.index')->with([
                'status' => 'danger',
                'title' => 'Gagal',
                'message' => 'Data Gagal Diubah!'
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        // Proses delete
        $deleteProcess = $category->delete();

        // Redirect dengan pesan sukses atau gagal
        if ($deleteProcess) {
            return redirect()->route('category.index')->with([
                'status' => 'success',
                'title' => 'Berhasil',
                'message' => 'Data Berhasil Dihapus!'
            ]);
        } else {
            return redirect()->route('category.index')->with([
                'status' => 'danger',
                'title' => 'Gagal',
                'message' => 'Data Gagal Dihapus!'
            ]);
        }
    }
}
