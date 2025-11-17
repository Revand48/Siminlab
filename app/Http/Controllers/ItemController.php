<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ItemController extends Controller
{
    // Variabel untuk data umum

    protected $title = 'Item';
    protected $menu = 'item';
    protected $directory = 'admin.item';
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        // Menyiapkan array untuk dikirim ke view
        $data['title'] = $this->title;
        $data['menu'] = $this->menu;

        // Mengambil data dari database
        $data['items'] = Item::with('category')->latest()->get();

        // Me-return view beserta data
        return view($this->directory . '.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data['title'] = $this->title;
        $data['menu'] = $this->menu;

        // Ambil semua data kategori untuk dikirim ke view
        $data['categories'] = Category::all();

        return view($this->directory . '.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validasi data
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'category_id' => 'required',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'unique_code' => 'nullable|unique:items',
            'condition' => 'required',
        ]);

        // 2. Proses upload foto jika ada
        if ($request->hasFile('photo')) {
            $image = $request->file('photo');
            // Buat nama file unik berdasarkan waktu
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            // Pindahkan file ke folder public/photos
            $image->move(public_path('photos'), $imageName);
            // Simpan nama file ke dalam data yang akan divalidasi
            $validatedData['photo'] = $imageName;
        }

        // 3. Simpan data ke database
        $item = Item::create($validatedData);

        // 4. Redirect dengan pesan sukses
        if ($item) {
            return redirect()->route('item.index')->with([
                'status' => 'success',
                'title' => 'Berhasil',
                'message' => 'Data Berhasil Ditambahkan!'
            ]);
        } else {
            return redirect()->route('item.index')->with([
                'status' => 'danger',
                'title' => 'Gagal',
                'message' => 'Data Gagal Ditambahkan!'
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Item $item)
    {
        // Menyiapkan data umum
        $data['title'] = $this->title;
        $data['menu'] = $this->menu;

        // Mencari data item berdasarkan ID menggunakan Model Binding
        $data['item'] = $item;

        // Ambil semua data kategori untuk dikirim ke view
        $data['categories'] = Category::all();

        // Me-return view beserta data
        return view($this->directory . '.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $item)
    {
        // 1. Gunakan Route Model Binding, variabel $item sudah siap pakai
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'category_id' => 'required',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'unique_code' => 'nullable|unique:items,unique_code,' . $item->id,
            'condition' => 'required',
        ]);

        // 2. Siapkan data dari hasil validasi
        $updateData = $validatedData;

        // 3. Proses foto baru jika diupload: hapus yang lama, simpan yang baru
        if ($request->hasFile('photo')) {
            // Hapus foto lama jika ada
            if ($item->photo && File::exists(public_path('photos/' . $item->photo))) {
                File::delete(public_path('photos/' . $item->photo));
            }

            // Simpan foto baru dan tambahkan namanya ke data update
            $image = $request->file('photo');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('photos'), $imageName);
            $updateData['photo'] = $imageName;
        }

        // 4. Update data item di database
        $updateProcess = $item->update($updateData);

        // 5. Redirect dengan pesan status berdasarkan hasil update
        if ($updateProcess) {
            return redirect()->route('item.index')->with([
                'status' => 'success',
                'title' => 'Berhasil',
                'message' => 'Data Berhasil Diubah!'
            ]);
        } else {
            return redirect()->route('item.index')->with([
                'status' => 'danger',
                'title' => 'Gagal',
                'message' => 'Data Gagal Diubah!'
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        // 1. Hapus file foto dari folder public jika ada
        if ($item->photo && File::exists(public_path('photos/' . $item->photo))) {
            File::delete(public_path('photos/' . $item->photo));
        }

        // 2. Hapus data item dari database
        $deleteProcess = $item->delete();

        // 3. Redirect dengan pesan status berdasarkan hasil proses hapus
        if ($deleteProcess) {
            return redirect()->route('item.index')->with([
                'status' => 'success',
                'title' => 'Berhasil',
                'message' => 'Data Berhasil Dihapus!'
            ]);
        } else {
            return redirect()->route('item.index')->with([
                'status' => 'danger',
                'title' => 'Gagal',
                'message' => 'Data Gagal Dihapus!'
            ]);
        }
    }
}
