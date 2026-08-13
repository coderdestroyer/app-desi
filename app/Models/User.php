<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;


    /*
    |--------------------------------------------------------------------------
    | MASS ASSIGNMENT
    |--------------------------------------------------------------------------
    |
    | Field yang boleh diisi melalui create() atau update().
    |
    */

    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar',
        'password',
        'role',
        'status',
        'two_factor_enabled',
    ];


    /*
    |--------------------------------------------------------------------------
    | HIDDEN ATTRIBUTES
    |--------------------------------------------------------------------------
    |
    | Field yang tidak ditampilkan ketika model dikonversi
    | menjadi array atau JSON.
    |
    */

    protected $hidden = [
        'password',
        'remember_token',
    ];


    /*
    |--------------------------------------------------------------------------
    | ATTRIBUTE CASTING
    |--------------------------------------------------------------------------
    |
    | email_verified_at diubah menjadi objek datetime.
    |
    | password menggunakan cast "hashed" sehingga Laravel
    | menangani hashing password secara otomatis.
    |
    */

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | ROLE CHECKER
    |--------------------------------------------------------------------------
    |
    | Pemeriksaan role secara umum.
    |
    | Contoh:
    |
    | auth()->user()->hasRole('admin');
    |
    */

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }


    /*
    |--------------------------------------------------------------------------
    | ADMIN CHECKER
    |--------------------------------------------------------------------------
    */

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }


    /*
    |--------------------------------------------------------------------------
    | OPERATOR CHECKER
    |--------------------------------------------------------------------------
    */

    public function isOperator(): bool
    {
        return $this->role === 'operator';
    }


    /*
    |--------------------------------------------------------------------------
    | USER CHECKER
    |--------------------------------------------------------------------------
    */

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public function projects(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function analysisResults(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AnalysisResult::class);
    }

    public function wilayahScopes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserWilayahScope::class);
    }

    /**
     * Cek apakah user berhak mengelola kabupaten tertentu (langsung atau via provinsi induk)
     */
    public function canAccessKabupaten(int $kabId): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (!\Illuminate\Support\Facades\Schema::hasTable('user_wilayah_scopes')) {
            return true;
        }

        $kabupaten = Kabupaten::find($kabId);
        if (!$kabupaten) {
            return false;
        }

        return $this->wilayahScopes()
            ->where(function ($q) use ($kabupaten) {
                $q->where('kabupaten_id', $kabupaten->kab_id)
                  ->orWhere(function ($q2) use ($kabupaten) {
                      $q2->where('provinsi_id', $kabupaten->provinsi_id)
                         ->whereNull('kabupaten_id');
                  });
            })
            ->exists();
    }

    /**
     * Cek apakah user berhak mengelola provinsi tertentu (langsung via scope provinsi atau via kabupaten induk)
     */
    public function canAccessProvinsi(int $provinsiId): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (!\Illuminate\Support\Facades\Schema::hasTable('user_wilayah_scopes')) {
            return true;
        }

        $scopes = $this->wilayahScopes()->get();

        if ($scopes->isEmpty()) {
            return true;
        }

        foreach ($scopes as $scope) {
            if ($scope->provinsi_id && $scope->provinsi_id == $provinsiId) {
                return true;
            }
            if ($scope->kabupaten_id) {
                $kab = Kabupaten::find($scope->kabupaten_id);
                if ($kab && $kab->provinsi_id == $provinsiId) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Cek apakah user berhak mengolah / merubah (CRUD) Data PDRB Provinsi tertentu.
     * Hanya Admin dan Operator dengan Scope Provinsi langsung yang berhak mengedit/menghapus.
     */
    public function canManageProvinsi(int $provinsiId): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (!\Illuminate\Support\Facades\Schema::hasTable('user_wilayah_scopes')) {
            return true;
        }

        $scopes = $this->wilayahScopes()->get();

        if ($scopes->isEmpty()) {
            return true;
        }

        return $this->wilayahScopes()
            ->where('provinsi_id', $provinsiId)
            ->whereNull('kabupaten_id')
            ->exists();
    }

    /**
     * Cek apakah user secara umum memiliki minimal 1 scope tingkat Provinsi (bukan sekadar scope Kabupaten).
     */
    public function hasProvinsiScope(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (!\Illuminate\Support\Facades\Schema::hasTable('user_wilayah_scopes')) {
            return true;
        }

        $scopes = $this->wilayahScopes()->get();

        if ($scopes->isEmpty()) {
            return true;
        }

        return $this->wilayahScopes()
            ->whereNotNull('provinsi_id')
            ->whereNull('kabupaten_id')
            ->exists();
    }
}