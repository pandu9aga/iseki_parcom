<?php

namespace App\Http\Controllers\Admins;

use App\Http\Controllers\Controller;
use App\Models\Tractor;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TractorController extends Controller
{
    public function index()
    {
        $page = "tractor";
        $tractors = Tractor::all();
        return view('admins.tractors.index', compact('page', 'tractors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'Type_Tractor' => 'required|string|max:255|unique:tractors,Type_Tractor',
        ]);

        Tractor::create($request->only('Type_Tractor'));

        return redirect()->route('tractor')->with('success', 'Tractor berhasil ditambahkan.');
    }

    public function update(Request $request, $Id_Tractor)
    {
        $tractor = Tractor::findOrFail($Id_Tractor);

        $request->validate([
            'Type_Tractor' => "required|string|max:255|unique:tractors,Type_Tractor,{$Id_Tractor},Id_Tractor",
        ]);

        $tractor->update($request->only('Type_Tractor'));

        return redirect()->route('tractor')->with('success', 'Tractor berhasil diperbarui.');
    }

    public function destroy($Id_Tractor)
    {
        $tractor = Tractor::findOrFail($Id_Tractor);
        $tractor->delete();

        return redirect()->route('tractor')->with('success', 'Tractor berhasil dihapus.');
    }
}