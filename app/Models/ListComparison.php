<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListComparison extends Model
{
    protected $table = 'list_comparisons';
    protected $primaryKey = 'Id_List_Comparison';
    public $timestamps = false;

    protected $fillable = ['Id_Comparison', 'Id_Tractor', 'Id_Part'];

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
}
