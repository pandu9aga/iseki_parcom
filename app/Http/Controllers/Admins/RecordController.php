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
            'Result_Record' => 'required'
        ]);

        $now = Carbon::now();

        DB::table('records')->insert(
            [
                'Id_Comparison' => $request->Id_Comparison,
                'Id_Tractor' => $request->Id_Tractor,
                'Id_Part' => $request->Id_Part,
                'Time_Record' => $now,
                'No_Tractor_Record' => $request->No_Tractor_Record,
                'Result_Record' => $request->Result_Record,
            ]
        );

        return redirect()->route('dashboard.admin');
    }
}

