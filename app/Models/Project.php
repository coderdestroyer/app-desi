<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'kabupaten_id',
        'kecamatan_id',
        'sektor_id',
        'alamat_lokasi',
        'nama_proyek',
        'deskripsi',
        'tahun_awal',
        'jangka_waktu_tahun',
        'status_publikasi',
        'pl_persentase_pajak_penghasilan',
        'pl_persentase_pajak_daerah',
        'pl_persentase_bot_bgs_fee',
        'pl_nominal_bunga',
        'pl_nominal_depresiasi',
        'rasio_modal_sendiri',
        'rasio_pinjaman_kredit',
        'suku_bunga_kredit',
        'tenor_kredit_tahun',
    ];

    protected $attributes = [
        'rasio_modal_sendiri' => 60.00,
        'rasio_pinjaman_kredit' => 40.00,
        'suku_bunga_kredit' => 8.05,
        'tenor_kredit_tahun' => 5,
    ];

    public function getRasioModalSendiriAttribute($value): float
    {
        return (float) ($value && (float)$value > 0 ? $value : 60.00);
    }

    public function getRasioPinjamanKreditAttribute($value): float
    {
        return (float) ($value && (float)$value > 0 ? $value : 40.00);
    }

    public function getSukuBungaKreditAttribute($value): float
    {
        return (float) ($value && (float)$value > 0 ? $value : 8.05);
    }

    public function getTenorKreditTahunAttribute($value): int
    {
        return (int) ($value && (int)$value > 0 ? $value : 5);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kabupaten(): BelongsTo
    {
        return $this->belongsTo(Kabupaten::class, 'kabupaten_id', 'kab_id');
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id', 'id');
    }

    public function sektor(): BelongsTo
    {
        return $this->belongsTo(Sektor::class, 'sektor_id', 'sektor_id');
    }

    public function capexComponents(): HasMany
    {
        return $this->hasMany(CapexComponent::class);
    }

    public function plComponents(): HasMany
    {
        return $this->hasMany(PlComponent::class);
    }
}
