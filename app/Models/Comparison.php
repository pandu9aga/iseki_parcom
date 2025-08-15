<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comparison extends Model
{
    protected $table = 'comparisons';
    protected $primaryKey = 'Id_Comparison';
    public $timestamps = false;

    protected $fillable = ['Name_Comparison', 'Id_Model'];

    public function model()
    {
        return $this->belongsTo(ModelAi::class, 'Id_Model', 'Id_Model');
    }

    public function list_comparison()
    {
        return $this->hasMany(ListComparison::class, 'Id_Comparison', 'Id_Comparison');
    }
}
