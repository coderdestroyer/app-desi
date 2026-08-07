<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SummaryShiftShareResult extends Model
{
    protected $table = 'summary_shift_share_results';

    protected $fillable = [
        'tingkat_wilayah',
        'provinsi_id',
        'kabupaten_id',
        'sektor_id',
        'tahun_awal',
        'tahun_akhir',
        'n_nij',
        'c_cij',
        's_sij',
        'd_dij',
        'keunggulan_kompetitif',
        'spesialisasi',
    ];

    protected $casts = [
        'n_nij' => 'float',
        'c_cij' => 'float',
        's_sij' => 'float',
        'd_dij' => 'float',
        'keunggulan_kompetitif' => 'boolean',
        'spesialisasi' => 'boolean',
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
