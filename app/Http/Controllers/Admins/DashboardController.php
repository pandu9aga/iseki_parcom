<?php

namespace App\Http\Controllers\Admins;

use App\Http\Controllers\Controller;
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

class DashboardController extends Controller
{
    public function index(Request $request) // Tambahkan parameter Request
    {
        $page = 'dashboard';

        // Ambil daftar Comparison untuk dropdown
        $availableComparisons = Comparison::whereIn('Id_Comparison', [1, 2, 3])->get();

        // Ambil Id_Comparison dari query string, default ke 1
        $selectedComparisonId = $request->query('comparison', 1);
        // Pastikan Id yang dipilih valid
        if (!in_array($selectedComparisonId, [1, 2, 3])) {
            $selectedComparisonId = 1;
        }

        $date = Carbon::today();

        $records = Record::whereDate('Time_Record', $date)
            ->where('Id_Comparison', $selectedComparisonId)
            ->with('comparison', 'tractor', 'part', 'user')
            ->get();

        $dateFormatted = Carbon::parse($date)->isoFormat('YYYY-MM-DD');

        return view('admins.dashboards.index', compact('page', 'records', 'dateFormatted', 'availableComparisons', 'selectedComparisonId'));
    }

    public function submit(Request $request)
    {
        $page = 'dashboard';

        $date = $request->input('Day_Record');
        $selectedComparisonId = $request->input('Id_Comparison', 1); // Ambil dari input hidden atau default ke 1

        // Ambil daftar Comparison untuk dropdown
        $availableComparisons = Comparison::whereIn('Id_Comparison', [1, 2, 3])->get();

        $records = Record::whereDate('Time_Record', $date)
            ->where('Id_Comparison', $selectedComparisonId) // Filter berdasarkan Id_Comparison
            ->with('comparison', 'tractor', 'part', 'user')
            ->get();

        $dateFormatted = Carbon::parse($date)->isoFormat('YYYY-MM-DD');

        return view('admins.dashboards.index', compact('page', 'records', 'dateFormatted', 'availableComparisons', 'selectedComparisonId'));
    }

    public function export(Request $request) {
        $date = $request->input('Day_Record_Hidden');
        $selectedComparisonId = $request->input('Id_Comparison_Hidden', 1); // Ambil dari input hidden atau default ke 1

        $dateParsed = Carbon::parse($date)->format('Y-m-d');
        $records = Record::whereDate('Time_Record', $dateParsed)
            ->where('Id_Comparison', $selectedComparisonId) // Filter berdasarkan Id_Comparison
            ->with('comparison', 'tractor', 'part', 'user') // Pastikan 'user' di-load untuk approved_by
            ->get();

        // Buat Spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header kolom (dengan Approved By)
        $headers = ['No', 'No Tractor', 'Name Tractor', 'Comparison', 'Part Detection', 'Result', 'Time Record', 'Approved By'];
        $sheet->fromArray([$headers], NULL, 'A1');

        // Style header (tebal & background abu-abu)
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F4F4F']]
        ];
        $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

        // Isi data
        $row = 2;
        foreach ($records as $index => $record) {
            // Ambil nama user, kalau null kasih kosong
            $approvedBy = $record->user->Name_User ?? '';

            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $record->No_Tractor_Record);
            $sheet->setCellValue('C' . $row, $record->tractor->Type_Tractor);
            $sheet->setCellValue('D' . $row, $record->comparison->Name_Comparison);
            $sheet->setCellValue('E' . $row, $record->part->Code_Part);
            $sheet->setCellValue('F' . $row, $record->Result_Record);
            $sheet->setCellValue('G' . $row, Carbon::parse($record->Time_Record)->format('d-m-Y H:i:s'));
            $sheet->setCellValue('H' . $row, $approvedBy); // Kolom Approved By

            // Set warna untuk kolom Result
            $resultCell = 'F' . $row;
            if ($record->Result_Record === 'OK') {
                $sheet->getStyle($resultCell)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '008000']] // Hijau
                ]);
            } elseif ($record->Result_Record === 'NG-OK') {
                $sheet->getStyle($resultCell)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'de7e00']] // Oranye
                ]);
            } else {
                $sheet->getStyle($resultCell)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FF0000']] // Merah
                ]);
            }
            $row++;
        }

        // Simpan ke file
        $fileName = "Part_Comparator_Report_" . $dateParsed . "_" . $record->comparison->Name_Comparison . ".xlsx"; // Nama file berisi tanggal dan nama comparison
        $writer = new Xlsx($spreadsheet);
        $filePath = storage_path('app/public/' . $fileName); // Simpan di storage/app/public/
        $writer->save($filePath);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function reset(){
        // Hanya reset record untuk comparison yang dipilih? Atau semua?
        // Misalnya, untuk sementara, kita reset semua.
        Record::truncate();
        return redirect()->route('dashboard.admin');
    }

    public function approve(Request $request)
    {
        $record = Record::findOrFail($request->record_id);
        $record->Result_Record = 'NG-OK';
        $record->Id_User = session('Id_User'); // Ambil dari session custom
        $record->save();

        return redirect()->back()->with('success', 'Record berhasil di-approve!');
    }
}