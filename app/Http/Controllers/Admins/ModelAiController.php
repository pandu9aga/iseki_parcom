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
            'Path_Model' => 'required|string|max:255|unique:models,Path_Model',
        ]);

        // Validasi manual ekstensi file
        $this->validateFiles($request);

        $pathName = $request->Path_Model;

        // Buat folder jika belum ada
        if (!File::exists($pathName)) {
            File::makeDirectory($pathName, 0755, true);
        }

        // Simpan file
        $request->file('metadata')->move($pathName, 'metadata.json');
        $request->file('model_file')->move($pathName, 'model.json');
        $request->file('weights')->move($pathName, 'weights.bin');

        ModelAi::create([
            'Name_Model' => $request->Name_Model,
            'Path_Model' => $pathName,
        ]);

        return redirect()->route('model')->with('success', 'Model berhasil ditambahkan.');
    }

    public function update(Request $request, $Id_Model)
    {
        $model = ModelAi::findOrFail($Id_Model);
        $request->validate([
            'Name_Model' => 'required|string|max:255',
            'Path_Model' => "required|string|max:255|unique:models,Path_Model,{$Id_Model},Id_Model",
        ]);

        if (
            $request->hasFile('metadata') ||
            $request->hasFile('model_file') ||
            $request->hasFile('weights')
        ) {
            $this->validateFiles($request);
        }

        $oldPath = $model->Path_Model;
        $newPath = $request->Path_Model;

        $base = $this->getBasePath();
        $oldFullPath = $base . '/' . $oldPath;
        $newFullPath = $base . '/' . $newPath;

        // Jika path berubah
        if ($newPath !== $oldPath) {
            if (File::exists($newFullPath)) {
                return back()->withErrors(['Path_Model' => 'Path baru sudah digunakan oleh model lain.']);
            }
            if (File::exists($oldFullPath)) {
                File::move($oldFullPath, $newFullPath);
            }
        }

        $targetPath = $newPath === $oldPath ? $oldFullPath : $newFullPath;

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

        $model->update([
            'Name_Model' => $request->Name_Model,
            'Path_Model' => $newPath,
        ]);

        return redirect()->route('model')->with('success', 'Model berhasil diperbarui.');
    }

    public function destroy($Id_Model)
    {
        $model = ModelAi::findOrFail($Id_Model);
        $dir = $this->getBasePath() . '/' . $model->Path_Model;

        if (File::exists($dir)) {
            File::deleteDirectory($dir);
        }

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