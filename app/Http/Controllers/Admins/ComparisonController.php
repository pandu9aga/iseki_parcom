<?php

namespace App\Http\Controllers\Admins;

use App\Http\Controllers\Controller;
use App\Models\Comparison;
use App\Models\ModelAi;
use Illuminate\Http\Request;

class ComparisonController extends Controller
{
    public function index()
    {
        $page = "comparison";
        $comparisons = Comparison::with('model')->get();
        return view('admins.comparisons.index', compact('page', 'comparisons'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'Name_Comparison' => 'required|string|max:255',
            'Id_Model' => 'required|exists:models,Id_Model',
        ]);

        Comparison::create($request->only(['Name_Comparison', 'Id_Model']));

        return redirect()->route('comparison')->with('success', 'Comparison berhasil ditambahkan.');
    }

    public function update(Request $request, $Id_Comparison)
    {
        $comparison = Comparison::findOrFail($Id_Comparison);

        $request->validate([
            'Name_Comparison' => 'required|string|max:255',
            'Id_Model' => 'required|exists:models,Id_Model',
        ]);

        $comparison->update($request->only(['Name_Comparison', 'Id_Model']));

        return redirect()->route('comparison')->with('success', 'Comparison berhasil diperbarui.');
    }

    public function destroy($Id_Comparison)
    {
        $comparison = Comparison::findOrFail($Id_Comparison);
        $comparison->delete();

        return redirect()->route('comparison')->with('success', 'Comparison berhasil dihapus.');
    }
}