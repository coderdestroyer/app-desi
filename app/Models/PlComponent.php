<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'tipe_kategori',
        'parent_id',
        'nama_komponen',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(PlComponent::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(PlComponent::class, 'parent_id');
    }

    public function yearlyData(): HasMany
    {
        return $this->hasMany(PlYearlyData::class);
    }
}
