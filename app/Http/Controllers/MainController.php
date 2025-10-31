<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

        // Ambil Name_Comparison dari tabel comparisons
        $comparison = DB::table('comparisons')->where('Id_Comparison', $request->Id_Comparison)->first();
        if (!$comparison) {
            return back()->withErrors(['Id_Comparison' => 'Comparison tidak ditemukan']);
        }

        $processName = $comparison->Name_Comparison; // Contoh: "Ring Synchronizer"
        $processName = strtolower(str_replace(' ', '_', $processName)); // "ring_synchronizer"
        $processName = 'parcom_' . $processName; // "parcom_ring_synchronizer"

        // --- LOGIKA UPDATE RECORD DI DATABASE PODIUM LANGSUNG ---
        // --- PERUBAHAN: Format sequence_no ---
        // Format No_Tractor_Record ke 5 digit dengan leading zero
        // Misal: "6731" -> "06731", "1" -> "00001", "12345" -> "12345"
        $sequenceNoFormatted = str_pad($request->No_Tractor_Record, 5, '0', STR_PAD_LEFT);
        $timestamp = $now->format('Y-m-d H:i:s');

        try {
            // 1. Cari plan di database PODIUM berdasarkan Sequence_No_Plan (dengan format yang disesuaikan)
            $plan = DB::connection('podium')->table('plans')->where('Sequence_No_Plan', $sequenceNoFormatted)->first();
            if (!$plan) {
                return back()->withErrors(['general' => "Plan dengan Sequence_No_Plan '{$sequenceNoFormatted}' tidak ditemukan di sistem PODIUM."]);
            }

            $modelName = $plan->Model_Name_Plan;

            // 2. Cari rule di database PODIUM berdasarkan Type_Rule = Model_Name_Plan
            $rule = DB::connection('podium')->table('rules')->where('Type_Rule', $modelName)->first();
            if (!$rule) {
                return back()->withErrors(['general' => "Rule untuk model '{$modelName}' tidak ditemukan di sistem PODIUM."]);
            }

            // 3. Decode Rule_Rule
            $ruleSequence = json_decode($rule->Rule_Rule, true);
            if (!is_array($ruleSequence)) {
                return back()->withErrors(['general' => "Format rule untuk model '{$modelName}' rusak."]);
            }

            // 4. Cek apakah process_name ada dalam rule
            $position = null;
            foreach ($ruleSequence as $key => $process) {
                if ($process === $processName) {
                    $position = (int)$key;
                    break;
                }
            }

            if ($position === null) {
                return back()->withErrors(['general' => "Proses '{$processName}' tidak termasuk dalam rule untuk model '{$modelName}'."]);
            }

            // 5. Decode Record_Plan
            $record = [];
            if ($plan->Record_Plan) {
                $decodedRecord = json_decode($plan->Record_Plan, true);
                if (is_array($decodedRecord)) {
                    $record = $decodedRecord;
                }
                // Jika tidak array atau null, biarkan $record tetap array kosong
            }

            // 6. Cek apakah proses sebelumnya sudah dilakukan
            $previousProcessesDone = true;
            $missingPrevious = [];
            for ($i = 1; $i < $position; $i++) {
                $prevProcess = $ruleSequence[$i] ?? null;
                if ($prevProcess && !isset($record[$prevProcess])) {
                    $previousProcessesDone = false;
                    $missingPrevious[] = $prevProcess;
                }
            }

            if (!$previousProcessesDone) {
                return back()->withErrors(['general' => "Proses sebelumnya belum selesai: " . implode(', ', $missingPrevious)]);
            }

            // 7. Update record: tambahkan proses dan timestamp
            $record[$processName] = $timestamp;

            // 8. Simpan kembali ke database PODIUM
            DB::connection('podium')->table('plans')
                ->where('Id_Plan', $plan->Id_Plan)
                ->update(['Record_Plan' => json_encode($record, JSON_UNESCAPED_UNICODE)]);

            // Logika berhasil dihilangkan, bisa ditambahkan jika perlu

        } catch (\Exception $e) {
            // Jika terjadi exception saat update database PODIUM
            return back()->withErrors(['general' => 'Gagal mencatat ke sistem PODIUM: ' . $e->getMessage()]);
        }

        // --- LOGIKA LAMA INSERT KE RECORDS DI PARCOM ---
        // Kalau hasil NG -> foto wajib diupload
        if ($request->Result_Record === "NG") {
            if ($request->hasFile('Photo_Ng_Path')) {
                $photoPath = $request->file('Photo_Ng_Path')->store('ng_photos', 'uploads');
            } else {
                return back()->withErrors(['Photo_Ng_Path' => 'Foto wajib diunggah jika hasil NG']);
            }
        }

        // Jika sukses update PODIUM, baru insert ke records PARCOM
        DB::table('records')->insert([
            'Id_Comparison'     => $request->Id_Comparison,
            'Id_Tractor'        => $request->Id_Tractor,
            'Id_Part'           => $request->Id_Part,
            'Time_Record'       => $now,
            'No_Tractor_Record' => $request->No_Tractor_Record, // Simpan nilai asli jika perlu
            'Result_Record'     => $request->Result_Record,
            'Photo_Ng_Path'     => $photoPath, // hanya terisi kalau NG
        ]);

        return redirect()->route('dashboard')->with('success', 'Record berhasil disimpan');
    }

    public function validateRule(Request $request)
    {
        // Validasi input
        $request->validate([
            'sequence_no' => 'required|string',
            'id_comparison' => 'required|integer'
        ]);

        $sequenceNo = $request->input('sequence_no');
        $idComparison = $request->input('id_comparison');

        // Ambil Name_Comparison dari tabel comparisons
        $comparison = DB::table('comparisons')->where('Id_Comparison', $idComparison)->first();
        if (!$comparison) {
            return response()->json([
                'success' => false,
                'message' => 'Comparison tidak ditemukan untuk Id_Comparison: ' . $idComparison
            ], 400);
        }

        $processName = $comparison->Name_Comparison;
        $processName = strtolower(str_replace(' ', '_', $processName));
        $processName = 'parcom_' . $processName;

        // --- LOGIKA VALIDASI URUTAN DARI DATABASE PODIUM ---
        // --- PERUBAHAN: Format sequence_no ---
        $sequenceNoFormatted = str_pad($sequenceNo, 5, '0', STR_PAD_LEFT);

        try {
            // 1. Cari plan di database PODIUM berdasarkan Sequence_No_Plan
            $plan = DB::connection('podium')->table('plans')->where('Sequence_No_Plan', $sequenceNoFormatted)->first();
            if (!$plan) {
                return response()->json([
                    'success' => false,
                    'message' => "Plan dengan Sequence_No_Plan '{$sequenceNoFormatted}' tidak ditemukan di sistem PODIUM."
                ], 404);
            }

            $modelName = $plan->Model_Name_Plan;

            // 2. Cari rule di database PODIUM berdasarkan Type_Rule
            $rule = DB::connection('podium')->table('rules')->where('Type_Rule', $modelName)->first();
            if (!$rule) {
                return response()->json([
                    'success' => false,
                    'message' => "Rule untuk model '{$modelName}' tidak ditemukan di sistem PODIUM."
                ], 400);
            }

            // 3. Ambil Rule_Rule (ini berupa string JSON dari Query Builder)
            $ruleSequenceRaw = $rule->Rule_Rule;

            // Coba decode string JSON menjadi array
            $ruleSequence = null;
            if (is_string($ruleSequenceRaw)) {
                $ruleSequence = json_decode($ruleSequenceRaw, true); // true untuk mengembalikan array asosiatif
            }

            // Pastikan $ruleSequence adalah array hasil decode JSON.
            if (!is_array($ruleSequence)) {
                // Jika decode gagal atau nilainya bukan string JSON valid, kembalikan error
                return response()->json([
                    'success' => false,
                    'message' => "Format rule untuk model '{$modelName}' rusak atau tidak valid."
                ], 400);
            }

            // 4. Cek apakah process_name ada dalam rule
            $position = null;
            foreach ($ruleSequence as $key => $process) {
                if ($process === $processName) {
                    $position = (int)$key;
                    break;
                }
            }

            if ($position === null) {
                return response()->json([
                    'success' => false,
                    'message' => "Proses '{$processName}' tidak termasuk dalam rule untuk model '{$modelName}'."
                ], 400);
            }

            // 5. Ambil Record_Plan (ini berupa string JSON dari Query Builder)
            $recordRaw = $plan->Record_Plan;

            // Coba decode string JSON menjadi array
            $record = [];
            if (is_string($recordRaw) && !empty($recordRaw)) {
                $decodedRecord = json_decode($recordRaw, true);
                if (is_array($decodedRecord)) {
                    $record = $decodedRecord;
                } else {
                    // Jika decode gagal atau nilainya bukan string JSON valid, kembalikan error
                    return response()->json([
                        'success' => false,
                        'message' => "Format Record_Plan untuk plan ini rusak."
                    ], 500); // atau 400, tergantung kebijakan
                }
            } // Jika null atau kosong, biarkan $record sebagai array kosong

            // 6. Cek apakah proses sebelumnya sudah dilakukan
            $previousProcessesDone = true;
            $missingPrevious = [];
            for ($i = 1; $i < $position; $i++) {
                $prevProcess = $ruleSequence[$i] ?? null;
                if ($prevProcess && !isset($record[$prevProcess])) {
                    $previousProcessesDone = false;
                    $missingPrevious[] = $prevProcess;
                }
            }

            if (!$previousProcessesDone) {
                return response()->json([
                    'success' => false,
                    'message' => "Proses sebelumnya belum selesai: " . implode(', ', $missingPrevious)
                ], 400);
            }

            // Jika semua validasi di atas lolos
            return response()->json([
                'success' => true,
                'message' => "Semua proses sebelumnya sudah selesai. Siap melanjutkan."
            ]);

        } catch (\Exception $e) {
            // Tangani exception umum
            return response()->json([
                'success' => false,
                'message' => 'Gagal memvalidasi rule di sistem PODIUM: ' . $e->getMessage()
            ], 500);
        }
    }

    public function insertold(Request $request)
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

