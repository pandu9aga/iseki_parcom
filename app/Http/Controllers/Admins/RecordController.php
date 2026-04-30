<?php

namespace App\Http\Controllers\Admins;

use App\Http\Controllers\Controller;
use App\Models\Comparison;
use App\Models\ListComparison;
use App\Models\Record;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecordController extends Controller
{
    public function ngRecord(Request $request)
    {
        $page = 'ng-record';

        if ($request->ajax()) {
            $records = Record::with('comparison', 'tractor', 'part', 'user')
                ->where('Result_Record', 'NG')
                ->select('records.*')
                ->orderBy('Time_Record', 'desc');

            return \Yajra\DataTables\Facades\DataTables::of($records)
                ->addIndexColumn()
                ->addColumn('tractor_name', function ($row) {
                    return $row->tractor_name;
                })
                ->addColumn('comparison_name', function ($row) {
                    return optional($row->comparison)->Name_Comparison ?? '-';
                })
                ->addColumn('part_code', function ($row) {
                    return optional($row->part)->Code_Part ?? '-';
                })
                ->editColumn('Time_Record', function ($row) {
                    return \Carbon\Carbon::parse($row->Time_Record)->format('d-m-Y H:i:s');
                })
                ->addColumn('action', function ($row) {
                    $photo = $row->Photo_Ng_Path ? asset('uploads/'.$row->Photo_Ng_Path) : null;
                    $photoTwo = $row->Photo_Ng_Path_Two ? asset('uploads/'.$row->Photo_Ng_Path_Two) : null;

                    return '<span class="badge bg-danger view-detail" data-bs-toggle="modal"
                        data-bs-target="#detailModal" data-id="'.$row->Id_Record.'"
                        data-no="'.($row->No_Tractor_Record ?? '-').'" data-type="'.($row->tractor_name).'"
                        data-comp="'.(optional($row->comparison)->Name_Comparison ?? '-').'"
                        data-part="'.(optional($row->part)->Code_Part ?? '-').'"
                        data-result="'.$row->Result_Record.'"
                        data-time="'.\Carbon\Carbon::parse($row->Time_Record)->format('d-m-Y H:i:s').'"
                        data-photo="'.$photo.'"
                        data-photo-two="'.$photoTwo.'"
                        data-text="'.($row->Text_Record ?? null).'"
                        data-predict="'.($row->Predict_Record ?? null).'" data-approve="true">
                        '.$row->Result_Record.'
                    </span>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('admins.records.ng_record', compact('page'));
    }

    public function record($Id_Comparison)
    {
        $page = 'record';

        $comparison = Comparison::where('Id_Comparison', $Id_Comparison)->with('model')->first();
        $list_comparisons = ListComparison::where('Id_Comparison', $Id_Comparison)->with('comparison', 'tractor', 'part')->get();

        // Joint Universal uses a different view with custom logic
        if ($Id_Comparison == 4) {
            return view('admins.records.joint_universal', compact('page', 'comparison', 'list_comparisons'));
        }

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
        if ($request->Result_Record === 'NG') {
            if ($request->hasFile('Photo_Ng_Path')) {
                $photoPath = $request->file('Photo_Ng_Path')->store('ng_photos', 'uploads');
            } else {
                return back()->withErrors(['Photo_Ng_Path' => 'Foto wajib diunggah jika hasil NG']);
            }
        }

        DB::table('records')->insert([
            'Id_Comparison' => $request->Id_Comparison,
            'Id_Tractor' => $request->Id_Tractor,
            'Id_Part' => $request->Id_Part,
            'Time_Record' => $now,
            'No_Tractor_Record' => $request->No_Tractor_Record,
            'Result_Record' => $request->Result_Record,
            'Photo_Ng_Path' => $photoPath, // hanya terisi kalau NG
        ]);

        return redirect()->route('dashboard.admin')->with('success', 'Record berhasil disimpan');
    }
}
