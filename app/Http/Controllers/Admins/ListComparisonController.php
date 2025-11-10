<?php

namespace App\Http\Controllers\Admins;

use App\Http\Controllers\Controller;
use App\Models\ListComparison;
use App\Models\Comparison;
use App\Models\Tractor;
use App\Models\Part;
use Illuminate\Http\Request;

class ListComparisonController extends Controller
{
    public function index()
    {
        $page = "list_comparison";
        $listComparisons = ListComparison::with(['comparison', 'tractor', 'part'])->get();
        return view('admins.list_comparisons.index', compact('page', 'listComparisons'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'Id_Comparison' => 'required|exists:comparisons,Id_Comparison',
            'Id_Tractor'    => 'required|exists:tractors,Id_Tractor',
            'Id_Part'       => 'required|exists:parts,Id_Part',
        ]);

        // Cek apakah kombinasi sudah ada (opsional, untuk hindari duplikat)
        $exists = ListComparison::where([
            ['Id_Comparison', $request->Id_Comparison],
            ['Id_Tractor', $request->Id_Tractor],
            ['Id_Part', $request->Id_Part],
        ])->exists();

        if ($exists) {
            return back()->withErrors(['duplicate' => 'Kombinasi Comparison, Tractor, dan Part ini sudah ada.']);
        }

        ListComparison::create($request->only(['Id_Comparison', 'Id_Tractor', 'Id_Part']));

        return redirect()->route('list.comparison')->with('success', 'List Comparison berhasil ditambahkan.');
    }

    public function update(Request $request, $Id_List_Comparison)
    {
        $list = ListComparison::findOrFail($Id_List_Comparison);

        $request->validate([
            'Id_Comparison' => 'required|exists:comparisons,Id_Comparison',
            'Id_Tractor'    => 'required|exists:tractors,Id_Tractor',
            'Id_Part'       => 'required|exists:parts,Id_Part',
        ]);

        // Cek duplikat (kecuali diri sendiri)
        $exists = ListComparison::where([
            ['Id_Comparison', $request->Id_Comparison],
            ['Id_Tractor', $request->Id_Tractor],
            ['Id_Part', $request->Id_Part],
        ])->where('Id_List_Comparison', '!=', $Id_List_Comparison)->exists();

        if ($exists) {
            return back()->withErrors(['duplicate' => 'Kombinasi tersebut sudah digunakan oleh data lain.']);
        }

        $list->update($request->only(['Id_Comparison', 'Id_Tractor', 'Id_Part']));

        return redirect()->route('list.comparison')->with('success', 'List Comparison berhasil diperbarui.');
    }

    public function destroy($Id_List_Comparison)
    {
        $list = ListComparison::findOrFail($Id_List_Comparison);
        $list->delete();

        return redirect()->route('list.comparison')->with('success', 'List Comparison berhasil dihapus.');
    }
}