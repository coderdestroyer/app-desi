<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SummaryLqResult extends Model
{
    protected $table = 'summary_lq_results';

    protected $fillable = [
        'tingkat_wilayah',
        'provinsi_id',
        'kabupaten_id',
        'sektor_id',
        'tahun',
        'nilai_lq',
        'kategori',
        'persen_daerah',
        'persen_acuan',
    ];

    protected $casts = [
        'nilai_lq' => 'float',
        'persen_daerah' => 'float',
        'persen_acuan' => 'float',
    ];

    public function provinsi(): BelongsTo
    {
        return $this->belongsTo(Provinsi::class, 'provinsi_id', 'provinsi_id');
    }

    public function kabupaten(): BelongsTo
    {
        return $this->belongsTo(Kabupaten::class, 'kabupaten_id', 'kab_id');
    }

    public function sektor(): BelongsTo
    {
        return $this->belongsTo(Sektor::class, 'sektor_id', 'sektor_id');
    }
}
