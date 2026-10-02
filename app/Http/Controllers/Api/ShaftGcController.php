<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ShaftGcController extends Controller
{
    /**
     * Get plan sequence based on QR Kanban code.
     */
    public function getPlan(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string'
        ]);

        $qrCode = $request->qr_code;
        
        // This is a placeholder logic for checking plan requirements.
        // In reality, this would query the `iseki_podium` database or APIs.
        // Assuming we mock or fetch the data appropriately here:
        
        $hasShaftGc = true; // Replace with actual check
        $modelName = 'gc_23'; // Replace with actual model name parse

        if (!$hasShaftGc) {
            return response()->json([
                'status' => 'success',
                'required' => false,
                'message' => 'Proses Shaft GC tidak diperlukan untuk QR ini.'
            ]);
        }

        $expectedText = '';
        if (str_contains(strtolower($modelName), 'gc') && str_contains($modelName, '23')) {
            $expectedText = 'gc_23';
        } elseif (str_contains(strtolower($modelName), 'gc') && str_contains($modelName, '25')) {
            $expectedText = 'gc_25';
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Model name tidak dikenali untuk Shaft GC: ' . $modelName
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'required' => true,
            'expected_text' => $expectedText,
            'model_name' => $modelName,
            'message' => 'Silakan lakukan scan Shaft GC.'
        ]);
    }

    /**
     * Save the Shaft GC scan result along with the photo.
     */
    public function saveResult(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
            'result_status' => 'required|in:OK,NG',
            'expected_text' => 'required|string',
            'prediction_text' => 'required|string',
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:5120' // Max 5MB
        ]);

        try {
            DB::beginTransaction();

            // Store the uploaded photo
            $path = $request->file('photo')->store('shaft_gc_photos', 'public');

            // DB::table('shaft_gc_results')->insert([...]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Hasil Shaft GC berhasil disimpan.',
                'photo_path' => $path
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan hasil: ' . $e->getMessage()
            ], 500);
        }
    }
}
