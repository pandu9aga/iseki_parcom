<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\Record;

class BearingKoyoController extends Controller
{
    public function validateRule(Request $request)
    {
        $request->validate([
            'sequence_no' => 'required|string',
            'id_comparison' => 'required|integer'
        ]);

        $sequenceNo = $request->input('sequence_no');
        $idComparison = $request->input('id_comparison');

        $comparison = DB::table('comparisons')->where('Id_Comparison', $idComparison)->first();
        if (!$comparison) {
            return response()->json([
                'success' => false,
                'message' => 'Comparison tidak ditemukan untuk Id_Comparison: ' . $idComparison
            ], 400);
        }

        $processName = strtolower(str_replace(' ', '_', $comparison->Name_Comparison));
        $processName = 'parcom_' . $processName;
        $sequenceNoFormatted = str_pad($sequenceNo, 5, '0', STR_PAD_LEFT);

        try {
            $plan = DB::connection('podium')->table('plans')->where('Sequence_No_Plan', $sequenceNoFormatted)->first();
            if (!$plan) {
                return response()->json([
                    'success' => false,
                    'message' => "Plan dengan Sequence_No_Plan '{$sequenceNoFormatted}' tidak ditemukan di sistem PODIUM."
                ], 404);
            }

            $modelName = $plan->Model_Name_Plan;
            $rule = DB::connection('podium')->table('rules')->where('Type_Rule', $modelName)->first();
            if (!$rule) {
                return response()->json([
                    'success' => false,
                    'message' => "Rule untuk model '{$modelName}' tidak ditemukan di sistem PODIUM."
                ], 400);
            }

            $ruleSequence = json_decode($rule->Rule_Rule, true);
            if (!is_array($ruleSequence)) {
                return response()->json([
                    'success' => false,
                    'message' => "Format rule untuk model '{$modelName}' rusak atau tidak valid."
                ], 400);
            }

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

            $recordRaw = $plan->Record_Plan;
            $record = [];
            if (is_string($recordRaw) && !empty($recordRaw)) {
                $decodedRecord = json_decode($recordRaw, true);
                if (is_array($decodedRecord)) {
                    $record = $decodedRecord;
                }
            }

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

            return response()->json([
                'success' => true,
                'message' => "Semua proses sebelumnya sudah selesai. Siap melanjutkan."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memvalidasi rule di sistem PODIUM: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getPartByTractorType($tractorType)
    {
        $list = \App\Models\ListComparison::with(['part', 'tractor'])
            ->whereHas('comparison', function ($q) {
                $q->where('Name_Comparison', 'Bearing KOYO');
            })
            ->whereHas('tractor', function ($q) use ($tractorType) {
                $q->whereRaw("? LIKE CONCAT(Type_Tractor, '%')", [$tractorType]);
            })
            ->first();

        if (!$list) {
            return response()->json(null);
        }

        return response()->json([
            'Id_Part' => $list->Id_Part,
            'Code_Part' => $list->part->Code_Part,
            'Name_Part' => $list->part->Name_Part,
            'Id_Tractor' => $list->Id_Tractor,
            'Id_Comparison' => $list->Id_Comparison,
        ]);
    }

    public function insert(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Id_Comparison' => 'required|integer',
            // 'Id_Tractor' => 'required|integer',
            // 'Id_Part' => 'required|integer',
            'No_Tractor_Record' => 'required|string',
            'Result_Record' => 'required|in:OK,NG',
            'Text_Record' => 'required|string', // Baru
            'Predict_Record' => 'required|string', // Baru
        ]);

        // Validasi foto
        if ($request->Result_Record === 'NG') {
            // Jika hasil NG di TFLite part, mungkin tidak ada foto OCR
            // Atau jika NG di OCR, pastikan ada foto part dan ocr
            // Sesuaikan logika validasi sesuai kebutuhan
            // Misalnya, jika part = shaft, maka Photo_Ng_Path_Two wajib
            if ($request->hasFile('Photo_Ng_Path_Two')) {
                 $validator->addRules(['Photo_Ng_Path_Two' => 'required|file|image|max:512']);
            }
             $validator->addRules(['Photo_Ng_Path' => 'required|file|image|max:512']); // Foto part selalu wajib jika proses lanjut

        } else { // Result_Record = OK
             $validator->addRules([
                 'Photo_Ng_Path' => 'required|file|image|max:512',
                 'Photo_Ng_Path_Two' => 'required|file|image|max:512', // Karena OK, pasti ada dua foto
             ]);
        }


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $now = Carbon::now();
        $photoPath1 = null;
        $photoPath2 = null;

        $comparison = DB::table('comparisons')->where('Id_Comparison', $request->Id_Comparison)->first();
        if (!$comparison) {
            return response()->json(['success' => false, 'message' => 'Comparison tidak ditemukan'], 400);
        }

        $processName = strtolower(str_replace(' ', '_', $comparison->Name_Comparison));
        $processName = 'parcom_' . $processName;
        $sequenceNoFormatted = str_pad($request->No_Tractor_Record, 5, '0', STR_PAD_LEFT);

        try {
            $plan = DB::connection('podium')->table('plans')->where('Sequence_No_Plan', $sequenceNoFormatted)->first();
            if (!$plan) {
                return response()->json(['success' => false, 'message' => "Plan tidak ditemukan di PODIUM"], 404);
            }

            $modelName = $plan->Model_Name_Plan;
            $rule = DB::connection('podium')->table('rules')->where('Type_Rule', $modelName)->first();
            if (!$rule) {
                return response()->json(['success' => false, 'message' => "Rule tidak ditemukan di PODIUM"], 400);
            }

            $ruleSequence = json_decode($rule->Rule_Rule, true);
            if (!is_array($ruleSequence)) {
                return response()->json(['success' => false, 'message' => "Format rule rusak"], 400);
            }

            $position = null;
            foreach ($ruleSequence as $key => $process) {
                if ($process === $processName) {
                    $position = (int)$key;
                    break;
                }
            }

            if ($position === null) {
                return response()->json(['success' => false, 'message' => "Proses tidak ada dalam rule"], 400);
            }

            $record = [];
            if ($plan->Record_Plan) {
                $decodedRecord = json_decode($plan->Record_Plan, true);
                if (is_array($decodedRecord)) {
                    $record = $decodedRecord;
                }
            }

            $previousProcessesDone = true;
            for ($i = 1; $i < $position; $i++) {
                $prevProcess = $ruleSequence[$i] ?? null;
                if ($prevProcess && !isset($record[$prevProcess])) {
                    $previousProcessesDone = false;
                    break;
                }
            }

            if (!$previousProcessesDone) {
                return response()->json(['success' => false, 'message' => "Proses sebelumnya belum selesai"], 400);
            }

            $record[$processName] = $now->format('Y-m-d H:i:s');
            $allCompleted = true;
            foreach ($ruleSequence as $proc) {
                if (!isset($record[$proc])) {
                    $allCompleted = false;
                    break;
                }
            }

            $updateData = ['Record_Plan' => json_encode($record, JSON_UNESCAPED_UNICODE)];
            if ($allCompleted) {
                $updateData['Status_Plan'] = 'done';
            }

            DB::connection('podium')->table('plans')
                ->where('Id_Plan', $plan->Id_Plan)
                ->update($updateData);

            // Simpan foto
            if ($request->hasFile('Photo_Ng_Path')) {
                $photoPath1 = $request->file('Photo_Ng_Path')->store('bearing_koyo_photos', 'uploads'); // Ganti folder jika perlu
            }
            if ($request->hasFile('Photo_Ng_Path_Two')) {
                $photoPath2 = $request->file('Photo_Ng_Path_Two')->store('bearing_koyo_photos', 'uploads');
            }

            // Simpan ke records PARCOM (pastikan struktur tabel mendukung kolom baru)
            DB::table('records')->insert([
                'Id_Comparison' => $request->Id_Comparison,
                // 'Id_Tractor' => $request->Id_Tractor,
                // 'Id_Part' => $request->Id_Part,
                'Time_Record' => $now,
                'No_Tractor_Record' => $request->No_Tractor_Record,
                'Result_Record' => $request->Result_Record,
                'Photo_Ng_Path' => $photoPath1, // Foto pertama
                'Photo_Ng_Path_Two' => $photoPath2, // Foto kedua (OCR)
                'Text_Record' => $request->Text_Record, // Teks dari OCR
                'Predict_Record' => $request->Predict_Record, // Prediksi dari OCR
            ]);

            return response()->json(['success' => true, 'message' => 'Record berhasil disimpan']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan: ' . $e->getMessage()], 500);
        }
    }

    public function index()
    {
        $date = Carbon::today();
        $records = Record::whereDate('Time_Record', $date)->where('Id_Comparison', 2)->with('comparison', 'tractor', 'part', 'user')->get();
        return response()->json($records);
    }
}