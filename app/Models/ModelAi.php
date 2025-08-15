<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModelAi extends Model
{
    protected $table = 'models';
    protected $primaryKey = 'Id_Model';
    public $timestamps = false;

    protected $fillable = ['Name_Model', 'Path_Model'];
}
