<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index() {
        try {
            $items = Item::all();
            return response()->json($items, 200);
        } catch (\Exception $e) {
            // Log 3: Gagal menghubungi basis data (Error)
            Log::error('Gagal mengambil daftar inventaris.', [
                'user' => auth()->check() ? auth()->user()->email : 'guest',
                'action' => 'fetch_items',
                'reason' => 'Koneksi ke basis data terputus atau gagal eksekusi query.',
                'error_message' => $e->getMessage()
            ]);
            
            return response()->json(['message' => 'Layanan basis data sedang bermasalah'], 503);
        }
    }

    public function store(Request $request) {
        // Menggunakan Validator manual agar bisa mencetak log saat gagal
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'location' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            // Log 2: Validasi gagal (Warning)
            Log::warning('Validasi penambahan inventaris gagal.', [
                'user' => auth()->check() ? auth()->user()->email : 'guest',
                'action' => 'validate_store_item',
                'invalid_fields' => $validator->errors()->keys(),
                'reason' => 'Input pengguna tidak memenuhi kriteria aturan validasi.'
            ]);
            
            return response()->json([
                'message' => 'Validasi gagal', 
                'errors' => $validator->errors()
            ], 422);
        }

        $item = Item::create($validator->validated());
        
        // Log 1: Berhasil menyimpan data (Info)
        Log::info('Berhasil menambahkan item inventaris.', [
            'user' => auth()->check() ? auth()->user()->email : 'guest',
            'action' => 'store_item',
            'item_name' => $item->name,
            'reason' => 'Data input valid dan berhasil disimpan ke basis data.'
        ]);

        return response()->json($item, 201);
    }

    public function show(Item $item) {
        return response()->json($item, 200);
    }

    public function update(Request $request, Item $item) {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'quantity' => 'sometimes|required|integer|min:0',
            'location' => 'sometimes|required|string|max:255',
        ]);

        $item->update($validated);
        return response()->json($item, 200);
    }

    public function destroy(Item $item) {
        $item->delete();
        return response()->json(null, 204);
    }
}