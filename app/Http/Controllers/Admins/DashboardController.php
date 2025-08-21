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
    public function index()
    {
        $page = 'dashboard';

        $date = Carbon::today();
        $records = Record::whereDate('Time_Record', $date)->with('comparison', 'tractor', 'part')->get();

        $date = Carbon::parse($date)->isoFormat('YYYY-MM-DD');

        return view('admins.dashboards.index', compact('page', 'records', 'date'));
    }

    public function submit(Request $request){
        $page = 'dashboard';

        $date = $request->input('Day_Record');
        $records = Record::whereDate('Time_Record', $date)->with('comparison', 'tractor', 'part')->get();

        $date = Carbon::parse($date)->isoFormat('YYYY-MM-DD');

        return view('admins.dashboards.index', compact('page', 'records', 'date'));
    }

    public function export(Request $request) {
        $date = $request->input('Day_Record_Hidden');
        $date = Carbon::parse($date)->format('Y-m-d H:i:s');
        $records = Record::whereDate('Time_Record', $date)->with('comparison', 'tractor', 'part')->get();

        // Buat Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header kolom
        $headers = ['No', 'No Tractor', 'Name Tractor', 'Comparison', 'Part Detection', 'Result', 'Time Record'];
        $sheet->fromArray([$headers], NULL, 'A1');

        // Style header (tebal & background abu-abu)
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F4F4F']]
        ];
        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);

        // Isi data
        $row = 2;
        foreach ($records as $index => $record) {
            // Tambahkan data ke Excel
            $sheet->fromArray([
                $index + 1,
                $record->No_Tractor_Record,
                $record->tractor->Type_Tractor,
                $record->comparison->Name_Comparison,
                $record->part->Code_Part,
                $record->Result_Record,
                Carbon::parse($record->Time_Record)->format('d-m-Y H:i:s')
            ], NULL, 'A' . $row);

            // Set warna dan tebal untuk "Correct" & "Incorrect"
            $correctnessCell = 'F' . $row;
            if ($record->Result_Record === 'OK') {
                $sheet->getStyle($correctnessCell)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '008000']] // Hijau
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
        return redirect()->route('dashboard.admin');
    }
}

