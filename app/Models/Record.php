<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    protected $table = 'records';
    protected $primaryKey = 'Id_Record';
    public $timestamps = false;

    protected $appends = ['tractor_name'];

    protected $fillable = [
        'Id_Comparison',
        'Id_Tractor',
        'Id_Part',
        'No_Tractor_Record',
        'Production_Date_Record',
        'Result_Record',
        'Time_Record',
        'Photo_Ng_Path',
        'Photo_Ng_Path_Two',
        'Text_Record',
        'Predict_Record',
        'Id_User'
    ];

    public function comparison()
    {
        return $this->belongsTo(Comparison::class, 'Id_Comparison', 'Id_Comparison');
    }

    public function tractor()
    {
        return $this->belongsTo(Tractor::class, 'Id_Tractor', 'Id_Tractor');
    }

    public function part()
    {
        return $this->belongsTo(Part::class, 'Id_Part', 'Id_Part');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'Id_User', 'Id_User');
    }

    public function getPlanAttribute()
    {
        // Ambil No_Produksi dari record ini
        $noProduksi = $this->No_Tractor_Record;
        $productionDate = $this->Production_Date_Record;

        $query = Plan::query();

        // Buat hanya lpad jika tidak mengandung 'T' saja
        if (strpos(strtoupper($noProduksi), 'T') === false) {
            $query->whereRaw('LPAD(?, 5, "0") = Sequence_No_Plan', [$noProduksi]);
        } else {
            $query->where('Sequence_No_Plan', $noProduksi);
        }

        // Match Production_Date_Record = Production_Date_Plan
        if (!empty($productionDate)) {
            $query->where('Production_Date_Plan', $productionDate);
        } else {
            // Fallback for old records: ambil yang terbaru berdasarkan sequence
            $query->orderBy('Id_Plan', 'desc');
        }

        return $query->first(); // Akan mengembalikan objek Plan atau null
    }

    public function getTractorNameAttribute()
    {
        return $this->plan->Model_Name_Plan ?? '-';
    }
}
