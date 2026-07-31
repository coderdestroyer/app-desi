<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KelurahanDesa extends Model
{
    use HasFactory;

    protected $table = 'kelurahan_desa';

    protected $fillable = [
        'kecamatan_id',
        'nama_desa',
    ];

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id', 'id');
    }
}
