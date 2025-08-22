<?php
namespace App\Http\Controllers\Admins;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Comparison;
use App\Models\ListComparison;

class RecordController extends Controller
{
    public function record($Id_Comparison)
    {
        $page = 'record';

        $comparison = Comparison::where('Id_Comparison', $Id_Comparison)->with('model')->first();
        $list_comparisons = ListComparison::where('Id_Comparison', $Id_Comparison)->with('comparison', 'tractor', 'part')->get();
        return view('admins.records.index', compact('page', 'comparison', 'list_comparisons'));
    }

    public function insert(Request $request)
    {
        $request->validate([
            'Id_Comparison' => 'required',
            'Id_Tractor' => 'required',
            'Id_Part' => 'required',
            'No_Tractor_Record' => 'required',
            'Result_Record' => 'required',
            'Photo_Ng_Path' => 'nullable|file|image',
        ]);

        $now = Carbon::now();
        $photoPath = null;

        // Kalau hasil NG -> foto wajib diupload
        if ($request->Result_Record === "NG") {
            if ($request->hasFile('Photo_Ng_Path')) {
                $photoPath = $request->file('Photo_Ng_Path')->store('ng_photos', 'uploads');
            } else {
                return back()->withErrors(['Photo_Ng_Path' => 'Foto wajib diunggah jika hasil NG']);
            }
        }

        DB::table('records')->insert([
            'Id_Comparison'     => $request->Id_Comparison,
            'Id_Tractor'        => $request->Id_Tractor,
            'Id_Part'           => $request->Id_Part,
            'Time_Record'       => $now,
            'No_Tractor_Record' => $request->No_Tractor_Record,
            'Result_Record'     => $request->Result_Record,
            'Photo_Ng_Path'     => $photoPath, // hanya terisi kalau NG
        ]);

        return redirect()->route('dashboard.admin')->with('success', 'Record berhasil disimpan');
    }
}

