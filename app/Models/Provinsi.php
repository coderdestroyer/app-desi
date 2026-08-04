<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Provinsi extends Model
{
    protected $table = 'provinsi';

    protected $primaryKey = 'provinsi_id';

    public $timestamps = false;

    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIP
    |--------------------------------------------------------------------------
    */

    public function kabupaten()
    {
        return $this->hasMany(Kabupaten::class, 'provinsi_id', 'provinsi_id');
    }

    public function pdrbProvinsi()
    {
        return $this->hasMany(PdrbSumateraProvinsi::class, 'provinsi_id', 'provinsi_id');
    }

    public function pdrbSumut()
    {
        return $this->hasMany(PdrbSumateraProvinsi::class, 'provinsi_id', 'provinsi_id');
    }
}