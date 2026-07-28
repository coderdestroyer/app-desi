<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CapexComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'parent_id',
        'nama_komponen',
        'volume',
        'satuan',
        'luas',
        'harga_m2',
    ];

    protected function casts(): array
    {
        return [
            'volume' => 'decimal:4',
            'luas' => 'decimal:4',
            'harga_m2' => 'decimal:2',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(CapexComponent::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(CapexComponent::class, 'parent_id');
    }
}
