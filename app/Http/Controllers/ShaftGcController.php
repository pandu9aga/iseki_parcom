<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\Record;

class ShaftGcController extends Controller
{
    public function plan(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
        ]);

        $qrCode = $request->input('qr_code');
        
        // Parse QR Code (Assuming format: 0754120261030...)
        if (strlen($qrCode) < 13) {
            return response()->json([
                'status' => 'error',
                'message' => 'Format QR Code tidak valid.'
            ]);
        }

        $sequenceNo = substr($qrCode, 0, 5);
        $productionDate = substr($qrCode, 5, 8);

        // Conditional requirement as per user request:
        // parcom_shaft_gc is only required for Production_Date_Plan >= 20261030 and Sequence_No_Plan >= 07541
        // But the controller just needs to check if it's in the rule for that plan.
        
        $plan = DB::connection('podium')->table('plans')
            ->where('Sequence_No_Plan', $sequenceNo)
            ->where('Production_Date_Plan', $productionDate)
            ->first();

        if (!$plan) {
            return response()->json([
                'status' => 'error',
                'message' => "Plan tidak ditemukan untuk Sequence {$sequenceNo} dan Date {$productionDate}."
            ]);
        }

        $modelName = $plan->Model_Name_Plan;
        
        // Check rule
        $rule = DB::connection('podium')->table('rules')->where('Type_Rule', $modelName)->first();
        if (!$rule) {
            return response()->json([
                'status' => 'error',
                'message' => "Rule untuk model '{$modelName}' tidak ditemukan."
            ]);
        }

        $ruleSequence = json_decode($rule->Rule_Rule, true);
        $processName = 'parcom_shaft_gc';
        
        // Filter out based on requirement (already handled in podium's dashboard, but let's be strict here too)
        $isRequired = in_array($processName, $ruleSequence);
        
        if ((int)$productionDate < 20261030) {
            $isRequired = false;
        } elseif ((int)$productionDate == 20261030 && (int)$sequenceNo < 7541) {
            $isRequired = false;
        }

        if (!$isRequired) {
            return response()->json([
                'status' => 'success',
                'required' => false,
                'message' => 'Proses parcom_shaft_gc tidak dibutuhkan untuk traktor ini.'
            ]);
        }

        // Determine expected text
        $modelNameLower = strtolower($modelName);
        $expectedText = '';
        if (strpos($modelNameLower, 'gc') !== false && strpos($modelNameLower, '23') !== false) {
            $expectedText = 'gc_23';
        } elseif (strpos($modelNameLower, 'gc') !== false && strpos($modelNameLower, '25') !== false) {
            $expectedText = 'gc_25';
        }

        if (empty($expectedText)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Model traktor bukan GC_23 atau GC_25, tidak perlu dicek.'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'required' => true,
            'expected_text' => $expectedText,
            'message' => 'Silakan ambil foto untuk divalidasi.'
        ]);
    }

    public function save(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
            'result_status' => 'required|string',
            'expected_text' => 'required|string',
            'prediction_text' => 'nullable|string',
            'photo' => 'nullable|file|image',
        ]);

        $qrCode = $request->input('qr_code');
        $sequenceNo = substr($qrCode, 0, 5);
        $productionDate = substr($qrCode, 5, 8);
        $timestamp = Carbon::now()->format('Y-m-d H:i:s');

        $plan = DB::connection('podium')->table('plans')
            ->where('Sequence_No_Plan', $sequenceNo)
            ->where('Production_Date_Plan', $productionDate)
            ->first();

        if (!$plan) {
            return response()->json(['status' => 'error', 'message' => 'Plan tidak ditemukan.']);
        }

        // Upload Photo
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $photoPath = $file->storeAs('ng_photos', $filename, 'public');
        }

        // Simpan ke database iseki_parcom records
        $record = new Record();
        $record->Id_Comparison = 5; // Shaft GC
        $record->Id_Tractor = 1; // Default atau ambil dari relasi (bisa disesuaikan, aplikasi lama menggunakan id list_comparisons)
        $record->Id_Part = 1; // Default
        $record->No_Tractor_Record = $sequenceNo;
        $record->Result_Record = $request->input('result_status');
        $record->Time_Record = $timestamp;
        $record->Id_User = 1; // Default sistem
        $record->Text_Record = $request->input('expected_text');
        $record->Predict_Record = $request->input('prediction_text');
        
        if ($photoPath) {
            $record->Photo_Ng_Path = $photoPath;
        }
        
        // Coba cari traktor berdasarkan nama model (kalau perlu untuk dashboard)
        $tractor = DB::table('tractors')->where('Type_Tractor', $plan->Model_Name_Plan)->first();
        if ($tractor) {
            $record->Id_Tractor = $tractor->Id_Tractor;
        }

        $record->save();

        // Update Podium Plans
        $recordRaw = $plan->Record_Plan;
        $recordArr = [];
        if (is_string($recordRaw) && !empty($recordRaw)) {
            $decodedRecord = json_decode($recordRaw, true);
            if (is_array($decodedRecord)) {
                $recordArr = $decodedRecord;
            }
        }
        
        $processName = 'parcom_shaft_gc';
        $recordArr[$processName] = [
            'status' => $request->input('result_status'),
            'timestamp' => $timestamp
        ];

        DB::connection('podium')->table('plans')
            ->where('Id_Plan', $plan->Id_Plan)
            ->update([
                'Record_Plan' => json_encode($recordArr),
                'Updated_At_Plan' => Carbon::now()
            ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil disimpan.'
        ]);
    }
    
    public function index()
    {
        $records = Record::with(['comparison', 'tractor', 'part', 'user'])
            ->where('Id_Comparison', 5) // Shaft GC
            ->orderBy('Id_Record', 'desc')
            ->get();
            
        // Map untuk menambahkan field tambahan dari tractor, part dll
        $mapped = $records->map(function($record) {
            $data = $record->toArray();
            $data['tractor_name'] = $record->tractor ? $record->tractor->Type_Tractor : '-';
            return $data;
        });

        return response()->json($mapped);
    }
}
