<?php

namespace App\Http\Controllers;

use App\Models\Kabupaten;
use App\Models\Provinsi;
use App\Models\Sektor;
use App\Services\ComparisonService;
use Illuminate\Http\Request;

class ComparisonController extends Controller
{
    public function __construct(
        private ComparisonService $comparisonService
    ) {
    }

    public function index(Request $request)
    {
        $provinsiId = $request->filled('provinsi') ? (int) $request->provinsi : 12;
        $kabId = $request->filled('kabupaten') ? (int) $request->kabupaten : null;

        if ($kabId && !$request->filled('provinsi')) {
            $selectedKab = Kabupaten::find($kabId);
            if ($selectedKab) {
                $provinsiId = $selectedKab->provinsi_id;
            }
        }

        $filter = [
            'provinsi'   => $provinsiId,
            'kabupaten'  => $kabId,
            'sektor'     => $request->sektor,
            'tahun_awal' => $request->tahun_awal ?? 2021,
            'tahun_akhir'=> $request->tahun_akhir ?? 2025,
        ];

        $dashboard = null;

        if (
            $filter['kabupaten'] &&
            $filter['sektor']
        ) {
            $dashboard = $this
                ->comparisonService
                ->getDashboard($filter);
        }

        $selectedKabupaten = $kabId ? Kabupaten::find($kabId) : null;
        $selectedSektor = $filter['sektor'] ? Sektor::find($filter['sektor']) : null;

        return view(
            'landing.comparison',
            [
                'provinsi' => Provinsi::orderBy(
                    'nama_provinsi'
                )->get(),

                'kabupaten' => Kabupaten::orderBy('nama_kabupaten')->get(),

                'sektor' => Sektor::orderBy(
                    'sektor_id'
                )->get(),

                'selectedKabupaten' => $selectedKabupaten,
                'selectedSektor' => $selectedSektor,

                'filter'=>$filter,

                'dashboard'=>$dashboard,

            ]
        );
    }
}