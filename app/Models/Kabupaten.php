<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kabupaten extends Model
{
    protected $table = 'kabupaten';

    protected $primaryKey = 'kab_id';

    public $timestamps = false;

    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIP
    |--------------------------------------------------------------------------
    */

    public function provinsi()
    {
        return $this->belongsTo(Provinsi::class, 'provinsi_id', 'provinsi_id');
    }

    public function pdrb()
    {
        return $this->hasMany(PdrbSumateraKabupaten::class, 'kabupaten_id', 'kab_id');
    }

    public function kecamatan()
    {
        return $this->hasMany(Kecamatan::class, 'kabupaten_id', 'kab_id');
    }
}