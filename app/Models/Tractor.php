<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tractor extends Model
{
    protected $table = 'tractors';
    protected $primaryKey = 'Id_Tractor';
    public $timestamps = false;

    protected $fillable = ['Type_Tractor'];
}
