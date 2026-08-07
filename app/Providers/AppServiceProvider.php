<?php

namespace App\Providers;

use App\Models\PdbNasional;
use App\Models\PdrbSumateraKabupaten;
use App\Models\PdrbSumateraProvinsi;
use App\Observers\PdbNasionalObserver;
use App\Observers\PdrbKabupatenObserver;
use App\Observers\PdrbProvinsiObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */     
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        PdrbSumateraProvinsi::observe(PdrbProvinsiObserver::class);
        PdrbSumateraKabupaten::observe(PdrbKabupatenObserver::class);
        PdbNasional::observe(PdbNasionalObserver::class);
    }
}

