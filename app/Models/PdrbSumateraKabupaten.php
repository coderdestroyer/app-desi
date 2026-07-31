<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdrbSumateraKabupaten extends Model
{
    protected $table = 'pdrb_sumatera_kabupaten';

    public $timestamps = false;

    protected $guarded = [];

    public function kabupaten()
    {
        return $this->belongsTo(Kabupaten::class, 'kabupaten_id', 'kab_id');
    }

    public function sektor()
    {
        return $this->belongsTo(Sektor::class, 'sektor_id');
    }
}
