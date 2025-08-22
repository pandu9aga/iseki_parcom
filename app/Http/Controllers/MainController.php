<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Comparison;
use App\Models\ListComparison;
use App\Models\Record;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

class MainController extends Controller
{
    public function index()
    {
        $page = 'dashboard';

        $date = Carbon::today();
        $records = Record::whereDate('Time_Record', $date)->with('comparison', 'tractor', 'part', 'user')->get();

        $date = Carbon::parse($date)->isoFormat('YYYY-MM-DD');

        return view('dashboards.index', compact('page', 'records', 'date'));
    }

    public function signin(){
        $page = 'dashboard';

        if (session()->has('Id_User')) {
            if (session('Id_Type_User') == 2){
                return redirect()->route('dashboard.admin');
            }
            else if (session('Id_Type_User') == 1){
                return redirect()->route('base');
            }
        }
        return view('auth.login', compact('page'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'Username_User' => 'required',
            'Password_User' => 'required'
        ]);

        $user = User::where('Username_User', $request->Username_User)->first();

        if (!$user) {
            return back()->withErrors(['loginError' => 'Invalid username or password']);
        }

        if ($request->Password_User == $user->Password_User) {
            session(['Id_User' => $user->Id_User]);
            session(['Id_Type_User' => $user->Id_Type_User]);
            session(['Username_User' => $user->Username_User]);
            if (session('Id_Type_User') == 2){
                return redirect()->route('dashboard.admin');
            }
            else if (session('Id_Type_User') == 1){
                return redirect()->route('base');
            }
        }

        return back()->withErrors(['loginError' => 'Invalid username or password']);
    }

    public function logout()
    {
        session()->forget('Id_User');
        session()->forget('Id_Type_User');
        session()->forget('Username_User');
        return redirect()->route('/');
    }

    public function submit(Request $request){
        $page = 'dashboard';

        $date = $request->input('Day_Record');
        $records = Record::whereDate('Time_Record', $date)->with('comparison', 'tractor', 'part', 'user')->get();

        $date = Carbon::parse($date)->isoFormat('YYYY-MM-DD');

        return view('dashboards.index', compact('page', 'records', 'date'));
    }

    public function export(Request $request) {
        $date = $request->input('Day_Record_Hidden');
        $date = Carbon::parse($date)->format('Y-m-d H:i:s');
        $records = Record::whereDate('Time_Record', $date)->with('comparison', 'tractor', 'part', 'user')->get();

        // Buat Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header kolom (tambahkan Approved By)
        $headers = ['No', 'No Tractor', 'Name Tractor', 'Comparison', 'Part Detection', 'Result', 'Time Record', 'Approved By'];
        $sheet->fromArray([$headers], NULL, 'A1');

        // Style header (tebal & background abu-abu)
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F4F4F']]
        ];
        $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

        // Isi data
        $row = 2;
        foreach ($records as $index => $record) {
            // Ambil nama user, kalau null kasih kosong
            $approvedBy = $record->user->Name_User ?? '';

            $sheet->fromArray([
                $index + 1,
                $record->No_Tractor_Record,
                $record->tractor->Type_Tractor,
                $record->comparison->Name_Comparison,
                $record->part->Code_Part,
                $record->Result_Record,
                Carbon::parse($record->Time_Record)->format('d-m-Y H:i:s'),
                $approvedBy
            ], NULL, 'A' . $row);

            // Set warna untuk kolom Result
            $correctnessCell = 'F' . $row;
            if ($record->Result_Record === 'OK') {
                $sheet->getStyle($correctnessCell)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '008000']] // Hijau
                ]);
            } elseif ($record->Result_Record === 'NG-OK') {
                $sheet->getStyle($correctnessCell)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'de7e00']] // Oranye
                ]);
            } else {
                $sheet->getStyle($correctnessCell)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FF0000']] // Merah
                ]);
            }

            $row++;
        }

        $date = Carbon::parse($date)->format('Y-m-d');

        // Simpan ke file
        $fileName = "Part_Comparator_Report_" . $date . ".xlsx";
        $writer = new Xlsx($spreadsheet);
        $filePath = public_path('storage/' . $fileName);
        $writer->save($filePath);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function reset(){
        Record::truncate();
        return redirect()->route('dashboard');
    }

    public function record($Id_Comparison)
    {
        $page = 'record';

        $comparison = Comparison::where('Id_Comparison', $Id_Comparison)->with('model')->first();
        $list_comparisons = ListComparison::where('Id_Comparison', $Id_Comparison)->with('comparison', 'tractor', 'part')->get();
        return view('records.index', compact('page', 'comparison', 'list_comparisons'));
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

        return redirect()->route('dashboard')->with('success', 'Record berhasil disimpan');
    }
}

