<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kabupaten extends Model
{
    protected $table = 'kabupaten';

    protected $primaryKey='kab_id';

    public $timestamps=false;

    protected $guarded=[];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIP
    |--------------------------------------------------------------------------
    */

    public function provinsi()
    {
        return $this->belongsTo(Provinsi::class,'provinsi_id');
    }

    public function pdrb()
    {
        return $this->hasMany(PdrbKabupaten::class,'kab_id');
    }

    public function hasilLq()
    {
        return $this->hasMany(HasilLq::class,'kab_id');
    }

    public function hasilSsa()
    {
        return $this->hasMany(HasilSsa::class,'kab_id');
    }

    public function tipologiSektor()
    {
        return $this->hasMany(HasilTipologiSektor::class,'kab_id');
    }

    public function indikator()
    {
        return $this->hasMany(IndikatorKabupaten::class,'kab_id');
    }

    public function tipologiKlassen()
    {
        return $this->hasMany(HasilTipologiKlassen::class,'kab_id');
    }

    public function kecamatan()
    {
        return $this->hasMany(Kecamatan::class, 'kabupaten_id', 'kab_id');
    }
}