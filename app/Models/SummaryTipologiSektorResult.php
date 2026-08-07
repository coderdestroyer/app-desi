<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SummaryTipologiSektorResult extends Model
{
    protected $table = 'summary_tipologi_sektor_results';

    protected $fillable = [
        'tingkat_wilayah',
        'provinsi_id',
        'kabupaten_id',
        'sektor_id',
        'tahun',
        'nilai_lq',
        'kategori_lq',
        'shift_share_net',
        'klasifikasi_sektor',
    ];

    protected $casts = [
        'nilai_lq' => 'float',
        'shift_share_net' => 'float',
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
