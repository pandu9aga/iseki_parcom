<?php

namespace App\Http\Controllers\Admins;

use App\Http\Controllers\Controller;
use App\Models\Part;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PartController extends Controller
{
    public function index()
    {
        $page = "part";
        $parts = Part::all();
        return view('admins.parts.index', compact('page', 'parts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'Name_Part' => 'required|string|max:255',
            'Code_Part' => 'required|string|max:100|unique:parts,Code_Part',
            'Code_Rack_Part' => 'required|string|max:100',
        ], [
            'Code_Part.unique' => 'Kode Part sudah digunakan.',
        ]);

        Part::create($request->only(['Name_Part', 'Code_Part', 'Code_Rack_Part']));

        return redirect()->route('part')->with('success', 'Part berhasil ditambahkan.');
    }

    public function update(Request $request, $Id_Part)
    {
        $part = Part::findOrFail($Id_Part);

        $request->validate([
            'Name_Part' => 'required|string|max:255',
            'Code_Part' => "required|string|max:100|unique:parts,Code_Part,{$Id_Part},Id_Part",
            'Code_Rack_Part' => 'required|string|max:100',
        ], [
            'Code_Part.unique' => 'Kode Part sudah digunakan.',
        ]);

        $part->update($request->only(['Name_Part', 'Code_Part', 'Code_Rack_Part']));

        return redirect()->route('part')->with('success', 'Part berhasil diperbarui.');
    }

    public function destroy($Id_Part)
    {
        $part = Part::findOrFail($Id_Part);
        $part->delete();

        return redirect()->route('part')->with('success', 'Part berhasil dihapus.');
    }
}