<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdrbSumut extends Model
{
    protected $table='pdrb_sumatera_provinsi';

    protected $primaryKey='pdrb_sumut_id';

    public $timestamps=false;

    protected $guarded=[];

    public function provinsi()
    {
        return $this->belongsTo(Provinsi::class,'provinsi_id');
    }

    public function sektor()
    {
        return $this->belongsTo(Sektor::class,'sektor_id');
    }

}