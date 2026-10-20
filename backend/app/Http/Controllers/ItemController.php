<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index() {
        return response()->json(Item::all(), 200);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'location' => 'required|string|max:255',
        ]); // Jika gagal, Laravel otomatis mereturn 422

        $item = Item::create($validated);
        return response()->json($item, 201); // 201 untuk data dibuat
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