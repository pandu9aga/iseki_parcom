<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    protected $table = 'records';
    protected $primaryKey = 'Id_Record';
    public $timestamps = false;

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

        // Konversi No_Produksi ke format 5 digit
        $noProduksi5Digit = str_pad($noProduksi, 5, '0', STR_PAD_LEFT);

        // Cari Plan yang sesuai
        // Gunakan koneksi 'podium' yang telah ditentukan di model Plan
        $plan = Plan::whereRaw('LPAD(?, 5, "0") = Sequence_No_Plan', [$noProduksi])
                    ->first(); // Menggunakan $noProduksi, bukan $noProduksi5Digit, karena LPAD akan menanganinya

        return $plan; // Akan mengembalikan objek Plan atau null
    }
}
