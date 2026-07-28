<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlYearlyData extends Model
{
    use HasFactory;

    protected $table = 'pl_yearly_data';

    protected $fillable = [
        'pl_component_id',
        'tahun_ke',
        'nilai',
    ];

    protected function casts(): array
    {
        return [
            'tahun_ke' => 'integer',
            'nilai' => 'decimal:2',
        ];
    }

    public function plComponent(): BelongsTo
    {
        return $this->belongsTo(PlComponent::class);
    }
}
