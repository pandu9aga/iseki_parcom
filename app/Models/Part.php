<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Part extends Model
{
    protected $table = 'parts';
    protected $primaryKey = 'Id_Part';
    public $timestamps = false;

    protected $fillable = ['Name_Part', 'Code_Part', 'Code_Rack_Part'];
}
