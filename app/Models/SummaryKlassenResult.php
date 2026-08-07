<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SummaryKlassenResult extends Model
{
    protected $table = 'summary_klassen_results';

    protected $fillable = [
        'tingkat_wilayah',
        'provinsi_id',
        'kabupaten_id',
        'sektor_id',
        'tahun_awal',
        'tahun_akhir',
        'growth_daerah',
        'growth_pembanding',
        'share_daerah',
        'share_pembanding',
        'kuadran',
        'kategori_kuadran',
    ];

    protected $casts = [
        'growth_daerah' => 'float',
        'growth_pembanding' => 'float',
        'share_daerah' => 'float',
        'share_pembanding' => 'float',
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
