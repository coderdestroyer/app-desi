<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tipologi extends Model
{
    protected $table = 'analisis_tipologi';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function kabupaten()
    {
        return $this->belongsTo(Kabupaten::class, 'kabupaten_id', 'kab_id');
    }

    public function provinsi()
    {
        return $this->belongsTo(Provinsi::class, 'provinsi_id', 'provinsi_id');
    }

    public function sektor()
    {
        return $this->belongsTo(Sektor::class, 'sektor_id', 'sektor_id');
    }
}

