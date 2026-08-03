<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserWilayahScope extends Model
{
    protected $table = 'user_wilayah_scopes';

    protected $fillable = [
        'user_id',
        'provinsi_id',
        'kabupaten_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function provinsi(): BelongsTo
    {
        return $this->belongsTo(Provinsi::class, 'provinsi_id', 'provinsi_id');
    }

    public function kabupaten(): BelongsTo
    {
        return $this->belongsTo(Kabupaten::class, 'kabupaten_id', 'kab_id');
    }

    public static function ensureTableExists(): bool
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('user_wilayah_scopes')) {
            try {
                \Illuminate\Support\Facades\Schema::create('user_wilayah_scopes', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->id();
                    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                    $table->foreignId('provinsi_id')->nullable()->constrained('provinsi', 'provinsi_id')->cascadeOnDelete();
                    $table->foreignId('kabupaten_id')->nullable()->constrained('kabupaten', 'kab_id')->cascadeOnDelete();
                    $table->timestamps();
                    $table->unique(['user_id', 'provinsi_id', 'kabupaten_id']);
                });
                return true;
            } catch (\Throwable $e) {
                return false;
            }
        }
        return true;
    }
}
