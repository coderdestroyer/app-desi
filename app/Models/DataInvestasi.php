<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataInvestasi extends Model
{
    protected $table = 'data_investasi';

    protected $guarded = [];

    public function provinsi()
    {
        return $this->belongsTo(Provinsi::class, 'provinsi_id', 'provinsi_id');
    }

    public function kabupaten()
    {
        return $this->belongsTo(Kabupaten::class, 'kabupaten_id', 'kab_id');
    }
}
