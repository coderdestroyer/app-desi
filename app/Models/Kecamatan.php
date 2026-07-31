<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kecamatan extends Model
{
    use HasFactory;

    protected $table = 'kecamatan';

    protected $fillable = [
        'kabupaten_id',
        'nama_kecamatan',
    ];

    public function kabupaten()
    {
        return $this->belongsTo(Kabupaten::class, 'kabupaten_id', 'kab_id');
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'kecamatan_id', 'id');
    }
}
