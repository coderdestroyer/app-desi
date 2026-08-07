<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SummaryIndikatorResult extends Model
{
    protected $table = 'summary_indikator_results';

    protected $fillable = [
        'tingkat_wilayah',
        'provinsi_id',
        'kabupaten_id',
        'sektor_id',
        'tahun',
        'pertumbuhan',
        'kontribusi',
    ];

    protected $casts = [
        'pertumbuhan' => 'float',
        'kontribusi' => 'float',
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
