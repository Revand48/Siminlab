<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\User;
use App\Models\Item;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    protected $title = 'Peminjaman';
    protected $menu = 'loan';
    protected $directory = 'admin.loan';

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Menyiapkan array untuk dikirim ke view
        $data['title'] = $this->title;
        $data['menu'] = $this->menu;

        // Mengambil data dari database
        $data['loans'] = Loan::with(['user', 'item'])->latest()->get();

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

        // Ambil data user yang rolenya siswa
        $data['users'] = User::where('role', 'Siswa')->get();

        // Ambil semua data item
        $data['items'] = Item::all();

        return view($this->directory . '.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validasi data
        $validatedData = $request->validate([
            'user_id' => 'required',
            'item_id' => 'required',
            'loan_date' => 'required|date',
            'return_date' => 'required|date|after_or_equal:loan_date',
        ]);

        // 2. Cek apakah barang sedang dipinjam atau rusak
        $item = Item::findOrFail($validatedData['item_id']);
        if ($item->condition === 'Rusak') {
            return redirect()->route('loan.create')->withInput()->with([
                'status' => 'danger',
                'title' => 'Gagal',
                'message' => 'Barang dalam kondisi rusak dan tidak bisa dipinjam.'
            ]);
        }

        $isBorrowed = Loan::where('item_id', $validatedData['item_id'])
            ->where('status', 'Dipinjam')
            ->exists();

        if ($isBorrowed) {
            return redirect()->route('loan.create')->withInput()->with([
                'status' => 'danger',
                'title' => 'Gagal',
                'message' => 'Barang ini sedang dipinjam oleh orang lain.'
            ]);
        }

        // 3. Simpan data ke database
        $loan = Loan::create($validatedData);

        // 4. Redirect dengan pesan sukses
        if ($loan) {
            return redirect()->route('loan.index')->with([
                'status' => 'success',
                'title' => 'Berhasil',
                'message' => 'Data Peminjaman Berhasil Ditambahkan!'
            ]);
        } else {
            return redirect()->route('loan.index')->with([
                'status' => 'danger',
                'title' => 'Gagal',
                'message' => 'Data Peminjaman Gagal Ditambahkan!'
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Loan $loan)
    {
        // 1. Gunakan Route Model Binding, variabel $loan sudah siap pakai.
        // 2. Siapkan data umum.
        $data['title'] = $this->title;
        $data['menu'] = $this->menu;

        // 3. Muat relasi (eager load)
        $loan->load(['user', 'item.category']);
        $data['loan'] = $loan;

        // 4. Kirim semua data ke view
        return view($this->directory . '.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Loan $loan)
    {
        $data['title'] = $this->title;
        $data['menu'] = $this->menu;

        // Cari data peminjaman berdasarkan ID menggunakan Model Binding
        $data['loan'] = $loan;

        // Ambil data user yang rolenya siswa
        $data['users'] = User::where('role', 'Siswa')->get();

        // Ambil semua data item
        $data['items'] = Item::all();

        return view($this->directory . '.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Loan $loan)
    {
        // 1. Gunakan Route Model Binding
        $validatedData = $request->validate([
            'user_id' => 'required',
            'item_id' => 'required',
            'loan_date' => 'required|date',
            'return_date' => 'required|date|after_or_equal:loan_date',
        ]);

        // 2. Jalankan pengecekan jika item berubah
        if ($loan->item_id != $validatedData['item_id']) {
            $newItem = Item::findOrFail($validatedData['item_id']);

            if ($newItem->condition === 'Rusak') {
                return redirect()->back()->withInput()->with([
                    'status' => 'danger',
                    'title' => 'Gagal',
                    'message' => 'Barang baru yang dipilih dalam kondisi rusak.'
                ]);
            }

            $isBorrowed = Loan::where('item_id', $validatedData['item_id'])
                ->where('status', 'Dipinjam')
                ->exists();

            if ($isBorrowed) {
                return redirect()->back()->withInput()->with([
                    'status' => 'danger',
                    'title' => 'Gagal',
                    'message' => 'Barang baru yang dipilih sedang dipinjam.'
                ]);
            }
        }

        // 3. Update data
        $updateProcess = $loan->update($validatedData);

        // 4. Redirect
        if ($updateProcess) {
            return redirect()->route('loan.index')->with([
                'status' => 'success',
                'title' => 'Berhasil',
                'message' => 'Data Peminjaman Berhasil Diubah!'
            ]);
        } else {
            return redirect()->route('loan.index')->with([
                'status' => 'danger',
                'title' => 'Gagal',
                'message' => 'Data Peminjaman Gagal Diubah!'
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Loan $loan)
    {
        $deleteProcess = $loan->delete();

        if ($deleteProcess) {
            return redirect()->route('loan.index')->with([
                'status' => 'success',
                'title' => 'Berhasil',
                'message' => 'Data Peminjaman Berhasil Dihapus!'
            ]);
        } else {
            return redirect()->route('loan.index')->with([
                'status' => 'danger',
                'title' => 'Gagal',
                'message' => 'Data Peminjaman Gagal Dihapus!'
            ]);
        }
    }

    public function returnItem(Loan $loan)
    {

        // 1. Gunakan Route Model Binding, variabel $loan sudah siap pakai.
        //    Tidak perlu lagi mencari manual dengan findOrFail().

        // 2. Cek apakah barang sudah pernah dikembalikan.

        if ($loan->status == 'Dikembalikan') {

            return redirect()->route('loan.index')->with([

                'status' => 'info',

                'title' => 'Informasi',

                'message' => 'Barang ini sudah dikembalikan sebelumnya.'

            ]);
        }



        // 3. Update status peminjaman dan tanggal pengembalian aktual.

        $loan->status = 'Dikembalikan';

        $loan->actual_return_date = now();

        $updateProcess = $loan->save();



        // 4. Redirect dengan pesan status berdasarkan hasil proses simpan.

        if ($updateProcess) {
            return redirect()->route('loan.index')->with([
                'status' => 'success',
                'title' => 'Berhasil',
                'message' => 'Barang telah berhasil dikembalikan!'
            ]);
        } else {
            return redirect()->route('loan.index')->with([
                'status' => 'danger',
                'title' => 'Gagal',
                'message' => 'Proses pengembalian barang gagal.'
            ]);
        }
    }
}
