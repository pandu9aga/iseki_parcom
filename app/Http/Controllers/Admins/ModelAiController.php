<?php

namespace App\Http\Controllers\Admins;

use App\Http\Controllers\Controller;
use App\Models\ModelAi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;


class ModelAiController extends Controller
{
    public function index()
    {
        $page = "model";
        $model = ModelAi::all();
        return view('admins.models.index', compact('page', 'model'));
    }

    protected function getBasePath()
    {
        return public_path('storage/model');
    }

    public function store(Request $request)
    {
        $request->validate([
            'Name_Model' => 'required|string|max:255',
            'Path_Model' => 'required|string|max:255|unique:models,Path_Model', // Validasi path unik di database
        ]);

        // Validasi file
        $this->validateFiles($request);

        // 🔥 Ekstrak nama folder dari path input
        $inputPath = $request->Path_Model;
        $folderName = basename($inputPath); // Ambil nama terakhir dari path: bearing_kbc

        // Pastikan folderName hanya berisi karakter yang valid
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $folderName)) {
            return back()->withErrors(['Path_Model' => 'Nama folder hanya boleh berisi huruf, angka, underscore, dan tanda hubung.']);
        }

        $fullPath = $this->getBasePath() . DIRECTORY_SEPARATOR . $folderName;

        // Buat folder jika belum ada
        if (!File::exists($fullPath)) {
            File::makeDirectory($fullPath, 0755, true);
        }

        // Simpan file ke folder yang benar
        $request->file('metadata')->move($fullPath, 'metadata.json');
        $request->file('model_file')->move($fullPath, 'model.json');
        $request->file('weights')->move($fullPath, 'weights.bin');

        // 🔥 Simpan path input asli ke database (../storage/model/bearing_kbc)
        ModelAi::create([
            'Name_Model' => $request->Name_Model,
            'Path_Model' => $request->Path_Model, // Simpan path lengkap seperti yang diinput user
        ]);

        return redirect()->route('model')->with('success', 'Model berhasil ditambahkan.');
    }

    public function update(Request $request, $Id_Model)
    {
        $model = ModelAi::findOrFail($Id_Model);

        $request->validate([
            'Name_Model' => 'required|string|max:255',
            'Path_Model' => "required|string|max:255|unique:models,Path_Model,{$Id_Model},Id_Model", // Abaikan model saat ini saat cek unique
        ]);

        if (
            $request->hasFile('metadata') ||
            $request->hasFile('model_file') ||
            $request->hasFile('weights')
        ) {
            $this->validateFiles($request);
        }

        // 🔥 Ekstrak nama folder dari path input baru
        $inputPath = $request->Path_Model;
        $newFolderName = basename($inputPath);

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $newFolderName)) {
            return back()->withErrors(['Path_Model' => 'Nama folder hanya boleh berisi huruf, angka, underscore, dan tanda hubung.']);
        }

        $oldPath = $model->Path_Model;
        $oldFolderName = basename($oldPath);

        $basePath = $this->getBasePath();
        $oldFullPath = $basePath . DIRECTORY_SEPARATOR . $oldFolderName;
        $newFullPath = $basePath . DIRECTORY_SEPARATOR . $newFolderName;

        // Jika path folder berubah
        if ($newFolderName !== $oldFolderName) {
            // Periksa apakah folder baru sudah ada dan digunakan model lain
            if (File::exists($newFullPath)) {
                $existingModel = ModelAi::where('Path_Model', 'like', "%{$newFolderName}")->first();
                if ($existingModel && $existingModel->Id_Model !== $Id_Model) {
                    return back()->withErrors(['Path_Model' => 'Path baru sudah digunakan oleh model lain.']);
                }
            }

            // Pindahkan folder jika lama ada
            if (File::exists($oldFullPath)) {
                File::move($oldFullPath, $newFullPath);
            }
        }

        $targetPath = $newFolderName === $oldFolderName ? $oldFullPath : $newFullPath;

        // Upload ulang file jika ada
        if ($request->hasFile('metadata')) {
            $request->file('metadata')->move($targetPath, 'metadata.json');
        }
        if ($request->hasFile('model_file')) {
            $request->file('model_file')->move($targetPath, 'model.json');
        }
        if ($request->hasFile('weights')) {
            $request->file('weights')->move($targetPath, 'weights.bin');
        }

        // 🔥 Simpan path input asli ke database (../storage/model/bearing_kbc)
        $model->update([
            'Name_Model' => $request->Name_Model,
            'Path_Model' => $request->Path_Model, // Simpan path lengkap seperti yang diinput user
        ]);

        return redirect()->route('model')->with('success', 'Model berhasil diperbarui.');
    }

    public function destroy($Id_Model)
    {
        $model = ModelAi::findOrFail($Id_Model);

        // 🔥 Ekstrak nama folder dari Path_Model
        $storedPath = $model->Path_Model; // Misalnya: "../storage/model/testing"
        $folderName = basename($storedPath); // Ambil bagian terakhir: "testing"

        // Pastikan nama folder hanya berisi karakter yang aman
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $folderName)) {
            // \Log::warning("Invalid folder name in Path_Model: $storedPath");
            return redirect()->back()->withErrors(['error' => 'Nama folder tidak valid.']);
        }

        // 🔥 Bangun path absolut ke folder yang sebenarnya
        $actualPath = public_path('storage/model') . DIRECTORY_SEPARATOR . $folderName;

        // \Log::info("Destroying model: {$model->Name_Model}, attempting to delete directory: $actualPath");

        if (File::exists($actualPath)) {
            try {
                File::deleteDirectory($actualPath);
                // \Log::info("Successfully deleted directory: $actualPath");
            } catch (\Exception $e) {
                // \Log::error("Failed to delete directory: $actualPath, Error: " . $e->getMessage());
                return redirect()->back()->withErrors(['error' => 'Gagal menghapus folder: ' . $e->getMessage()]);
            }
        } else {
            // \Log::warning("Directory does not exist, skipping deletion: $actualPath");
            // Opsional: Beri peringatan bahwa folder fisik tidak ditemukan, tapi tetap hapus dari DB
            // return redirect()->back()->withErrors(['error' => 'Folder fisik tidak ditemukan: ' . $actualPath]);
        }

        // Hapus data dari database
        $model->delete();

        return redirect()->route('model')->with('success', 'Model dan folder berhasil dihapus.');
    }

    protected function validateFiles(Request $request)
    {
        $allowed = ['json', 'bin'];

        foreach (['metadata', 'model_file'] as $field) {
            if ($request->hasFile($field)) {
                $ext = strtolower($request->file($field)->getClientOriginalExtension());
                if ($ext !== 'json') {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        $field => ['File harus berekstensi .json.']
                    ]);
                }
            }
        }

        if ($request->hasFile('weights')) {
            $ext = strtolower($request->file('weights')->getClientOriginalExtension());
            if ($ext !== 'bin') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'weights' => ['File harus berekstensi .bin.']
                ]);
            }
        }
    }
}