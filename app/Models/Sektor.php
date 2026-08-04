<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sektor extends Model
{
    protected $table = 'sektor';

    protected $primaryKey = 'sektor_id';

    public $timestamps = false;

    protected $guarded = [];
    
    public function getNamaAttribute(): ?string
    {
        return $this->attributes['nama_sektor'] ?? null;
    }

    public function pdbNasional()
    {
        return $this->hasMany(PdbNasional::class, 'sektor_id', 'sektor_id');
    }

    public function pdrbProvinsi()
    {
        return $this->hasMany(PdrbSumateraProvinsi::class, 'sektor_id', 'sektor_id');
    }

    public function pdrbKabupaten()
    {
        return $this->hasMany(PdrbSumateraKabupaten::class, 'sektor_id', 'sektor_id');
    }
}
