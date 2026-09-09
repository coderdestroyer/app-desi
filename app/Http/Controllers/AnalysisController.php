<?php

namespace App\Http\Controllers;

use App\Models\Kabupaten;
use App\Models\Provinsi;
use App\Services\DashboardAnalysisService;
use Illuminate\Http\Request;

class AnalysisController extends Controller
{
    public function __construct(
        private DashboardAnalysisService $dashboardService
    ) {}

    /**
     * ==========================================================
     * Dashboard Analisis
     * ==========================================================
     */
    public function index(
        Request $request
    ) {

        /**
         * ------------------------------------------
         * Filter
         * ------------------------------------------
         */

        $provinsiId = $request->filled('provinsi') ? $request->integer('provinsi') : null;
        $kabParam = $request->get('kabupaten');

        $kabId = null;
        $targetWilayahId = null;

        if ($kabParam) {
            if (str_starts_with((string) $kabParam, 'prov_')) {
                $targetWilayahId = (string) $kabParam;
                $pId = (int) str_replace('prov_', '', (string) $kabParam);
                if (!$provinsiId) {
                    $provinsiId = $pId;
                }
            } elseif (is_numeric($kabParam)) {
                $kabId = (int) $kabParam;
                $targetWilayahId = $kabId;
                if (!$provinsiId) {
                    $selectedKab = Kabupaten::find($kabId);
                    if ($selectedKab) {
                        $provinsiId = $selectedKab->provinsi_id;
                    }
                }
            }
        }

        $metode = $request->get(
            'metode',
            'lq'
        );

        $tahun = $request->get(
            'tahun',
            now()->year
        );

        /**
         * ------------------------------------------
         * Dropdown
         * ------------------------------------------
         */

        $provinsi =
            Provinsi::orderBy('nama_provinsi')
                ->get();

        $kabupaten =
            Kabupaten::orderBy('nama_kabupaten')
                ->get();

        /**
         * ------------------------------------------
         * Dashboard
         * ------------------------------------------
         */

        $dashboard = null;

        if ($targetWilayahId) {

            $dashboard =

                $this->dashboardService

                    ->getDashboard(

                        kabId: $targetWilayahId,

                        metode: $metode,

                        tahun: $tahun

                    );

        }

        /**
         * ------------------------------------------
         * View
         * ------------------------------------------
         */

        return view(

            'landing.analysis',

            [

                'dashboard'=>$dashboard,

                'provinsi'=>$provinsi,

                'kabupaten'=>$kabupaten,

                'filter'=>[

                    'provinsi'=>$provinsiId,

                    'kabupaten'=>$kabParam,

                    'metode'=>$metode,

                    'tahun'=>$tahun

                ]

            ]

        );

    }

}