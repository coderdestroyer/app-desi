<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdbNasional extends Model
{
    protected $table = 'pdb_nasional';

    public $timestamps = false;

    protected $guarded = [];

    public function sektor()
    {
        return $this->belongsTo(Sektor::class, 'sektor_id');
    }
}
