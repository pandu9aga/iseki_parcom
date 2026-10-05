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
        \Illuminate\Support\Facades\Log::info("Shaft GC Plan API hit with QR: " . $qrCode);
        
        $tractorType = '';
        
        // Coba pisahkan dengan titik koma jika ada
        if (strpos($qrCode, ';') !== false) {
            $parts = explode(';', $qrCode);
            if (count($parts) >= 2) {
                $sequenceNo = trim($parts[0]);
                $productionDate = trim($parts[1]);
                if (count($parts) >= 3) {
                    $tractorType = trim($parts[2]);
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Format QR Code salah (kurang dari 2 bagian dengan pemisah ;).'
                ]);
            }
        } else {
            // Asumsi format lama tanpa pemisah: 0754120261030...
            if (strlen($qrCode) < 13) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Format QR Code tidak valid (kurang dari 13 karakter).'
                ]);
            }
            $sequenceNo = substr($qrCode, 0, 5);
            $productionDate = substr($qrCode, 5, 8);
        }

        // Conditional requirement as per user request:
        // parcom_shaft_gc is only required for Production_Date_Plan >= 20261030 and Sequence_No_Plan >= 07541
        // But the controller just needs to check if it's in the rule for that plan.
        
        if (strpos(strtoupper($sequenceNo), 'T') !== false) {
            $sequenceNoFormatted = $sequenceNo;
        } else {
            $sequenceNoFormatted = str_pad($sequenceNo, 5, '0', STR_PAD_LEFT);
        }

        $plan = DB::connection('podium')->table('plans')
            ->where('Sequence_No_Plan', $sequenceNoFormatted)
            ->where('Production_Date_Plan', $productionDate)
            ->first();

        if (!$plan) {
            return response()->json([
                'status' => 'error',
                'message' => "Plan dengan Sequence_No_Plan '{$sequenceNoFormatted}' dan Date '{$productionDate}' tidak ditemukan di sistem PODIUM."
            ]);
        }

        $modelName = $plan->Model_Name_Plan;
        $planType = $plan->Type_Plan;
        
        $rule = DB::connection('podium')->table('rules')->where('Type_Rule', $planType)->first();
        if (!$rule) {
            $rule = DB::connection('podium')->table('rules')->where('Type_Rule', $modelName)->first();
        }

        if (!$rule) {
            return response()->json([
                'status' => 'error',
                'message' => "Rule untuk tipe '{$planType}' atau model '{$modelName}' tidak ditemukan di sistem PODIUM."
            ]);
        }

        $ruleSequence = json_decode($rule->Rule_Rule, true) ?? [];
        $processName = 'parcom_shaft_gc';
        $isRequired = in_array($processName, $ruleSequence);
        
        if (!$isRequired) {
            return response()->json([
                'status' => 'success',
                'required' => false,
                'message' => 'Proses parcom_shaft_gc tidak dibutuhkan untuk traktor ini.'
            ]);
        }

        $position = null;
        foreach ($ruleSequence as $key => $process) {
            if ($process === $processName) {
                $position = (int) $key;
                break;
            }
        }

        if ($position !== null && $position > 1) {
            $recordRaw = $plan->Record_Plan;
            $record = [];
            if (is_string($recordRaw) && !empty($recordRaw)) {
                $decodedRecord = json_decode($recordRaw, true);
                if (is_array($decodedRecord)) {
                    $record = $decodedRecord;
                }
            }

            $missingPrevious = [];
            for ($i = 1; $i < $position; $i++) {
                $prevProcess = $ruleSequence[$i] ?? null;
                // Use string key cast just in case
                if ($prevProcess === null && isset($ruleSequence[(string)$i])) {
                    $prevProcess = $ruleSequence[(string)$i];
                }
                if ($prevProcess && !isset($record[$prevProcess])) {
                    $missingPrevious[] = $prevProcess;
                }
            }

            if (!empty($missingPrevious)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Proses sebelumnya belum selesai: ' . implode(', ', $missingPrevious),
                ]);
            }
        }
        
        if ((int)$productionDate < 20261030) {
            $isRequired = false;
        } elseif ((int)$productionDate == 20261030 && (int)$sequenceNo < 7541) {
            $isRequired = false;
        }

        if (!$isRequired) {
            return response()->json([
                'status' => 'success',
                'required' => false,
                'message' => 'Proses parcom_shaft_gc tidak dibutuhkan untuk traktor ini (syarat tanggal).'
            ]);
        }

        // Determine expected text
        $modelNameLower = strtolower(isset($modelName) ? $modelName : $planType);
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
        // Coba pisahkan dengan titik koma jika ada
        if (strpos($qrCode, ';') !== false) {
            $parts = explode(';', $qrCode);
            if (count($parts) >= 2) {
                $sequenceNo = trim($parts[0]);
                $productionDate = trim($parts[1]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Format QR Code salah (kurang dari 2 bagian dengan pemisah ;).'
                ]);
            }
        } else {
            // Asumsi format lama tanpa pemisah: 0754120261030...
            if (strlen($qrCode) < 13) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Format QR Code tidak valid.'
                ]);
            }
            $sequenceNo = substr($qrCode, 0, 5);
            $productionDate = substr($qrCode, 5, 8);
        }
        $timestamp = Carbon::now()->format('Y-m-d H:i:s');

        if (strpos(strtoupper($sequenceNo), 'T') !== false) {
            $sequenceNoFormatted = $sequenceNo;
        } else {
            $sequenceNoFormatted = str_pad($sequenceNo, 5, '0', STR_PAD_LEFT);
        }

        $plan = DB::connection('podium')->table('plans')
            ->where('Sequence_No_Plan', $sequenceNoFormatted)
            ->where('Production_Date_Plan', $productionDate)
            ->first();

        // Upload Photo
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/shaft_gc_photos'), $filename);
            $photoPath = 'shaft_gc_photos/' . $filename;
        }

        // Simpan ke database iseki_parcom records
        $record = new Record();
        $record->Id_Comparison = 5; // Shaft GC
        $record->Id_Tractor = null; 
        $record->Id_Part = null; 
        $record->No_Tractor_Record = $sequenceNoFormatted;
        $record->Production_Date_Record = $productionDate;
        $record->Result_Record = $request->input('result_status');
        $record->Time_Record = $timestamp;
        $record->Id_User = null; 
        $record->Text_Record = $request->input('expected_text');
        $record->Predict_Record = $request->input('prediction_text');
        
        if ($photoPath) {
            $record->Photo_Ng_Path = $photoPath;
        }

        $record->save();

        // Update Podium Plans
        if ($plan) {
            $recordRaw = $plan->Record_Plan;
            $recordArr = [];
            if (is_string($recordRaw) && !empty($recordRaw)) {
                $decodedRecord = json_decode($recordRaw, true);
                if (is_array($decodedRecord)) {
                    $recordArr = $decodedRecord;
                }
            }
            
            $processName = 'parcom_shaft_gc';
            $recordArr[$processName] = $timestamp;

            DB::connection('podium')->table('plans')
                ->where('Id_Plan', $plan->Id_Plan)
                ->update([
                    'Record_Plan' => json_encode($recordArr)
                ]);
        }

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
