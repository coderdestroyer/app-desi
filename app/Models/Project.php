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
