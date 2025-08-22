<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    protected $table = 'records';
    protected $primaryKey = 'Id_Record';
    public $timestamps = false;

    protected $fillable = ['Id_Comparison', 'Id_Tractor', 'Id_Part', 'No_Tractor_Record', 'Result_Record', 'Time_Record', 'Photo_Ng_Path', 'Id_User'];

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
}
