<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\PlComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfitLossController extends Controller
{
    private function authorizeProjectOwner(Project $project): void
    {
        if ($project->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke proyek ini.');
        }
    }

    public function index(Request $request, Project $project)
    {
        $this->authorizeProjectOwner($project);

        $project->load(['capexComponents']);

        $totalCapex = 0;
        foreach ($project->capexComponents as $comp) {
            if ($comp->parent_id !== null) {
                $vol = $comp->volume && $comp->volume > 0 ? (float)$comp->volume : 1;
                $luas = $comp->luas && $comp->luas > 0 ? (float)$comp->luas : 0;
                $harga = $comp->harga_m2 ? (float)$comp->harga_m2 : 0;

                if ($luas > 0) {
                    $totalCapex += $vol * $luas * $harga;
                } else {
                    $totalCapex += $vol * $harga;
                }
            }
        }

        $components = $project->plComponents()
            ->with('yearlyData')
            ->orderBy('id')
            ->get();

        $pajakRaw = (float) $project->pl_persentase_pajak_penghasilan;
        $pajakPct = ($pajakRaw > 0) ? $pajakRaw : 22.00;

        $bungaRaw = (float) $project->suku_bunga_kredit;
        $bungaPct = ($bungaRaw > 0) ? $bungaRaw : 8.05;

        if ($request->wantsJson()) {
            return response()->json([
                'components' => $components,
                'total_capex' => $totalCapex,
                'settings' => [
                    'pl_persentase_pajak_penghasilan' => $pajakPct,
                    'pl_nominal_bunga' => (float) $project->pl_nominal_bunga,
                    'pl_nominal_depresiasi' => (float) $project->pl_nominal_depresiasi,
                    'rasio_modal_sendiri' => (float) ($project->rasio_modal_sendiri ?: 60),
                    'rasio_pinjaman_kredit' => (float) ($project->rasio_pinjaman_kredit ?: 40),
                    'suku_bunga_kredit' => $bungaPct,
                    'tenor_kredit_tahun' => (int) ($project->tenor_kredit_tahun ?: 5),
                ]
            ]);
        }

        return view('operator.peluang_investasi.projects.laba-rugi', compact('project', 'totalCapex'));
    }

    public function updateSettings(Request $request, Project $project)
    {
        $this->authorizeProjectOwner($project);

        $validated = $request->validate([
            'pl_persentase_pajak_penghasilan' => 'nullable|numeric|min:0|max:100',
            'pl_nominal_depresiasi' => 'nullable|numeric|min:0',
            'suku_bunga_kredit' => 'nullable|numeric|min:0|max:100',
        ]);

        $pajakInput = $request->input('pl_persentase_pajak_penghasilan');
        $pajakSave = (is_numeric($pajakInput) && (float)$pajakInput > 0) ? (float)$pajakInput : 22.00;

        $bungaInput = $request->input('suku_bunga_kredit');
        $bungaSave = (is_numeric($bungaInput) && (float)$bungaInput > 0) ? (float)$bungaInput : 8.05;

        $project->update([
            'pl_persentase_pajak_penghasilan' => $pajakSave,
            'pl_nominal_depresiasi' => $request->input('pl_nominal_depresiasi', 0),
            'suku_bunga_kredit' => $bungaSave,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan Laba Rugi berhasil disimpan!',
        ]);
    }

    public function store(Request $request, Project $project)
    {
        $this->authorizeProjectOwner($project);

        $request->validate([
            'components' => 'nullable|array',
            'components.*.temp_id' => 'required|string',
            'components.*.parent_temp_id' => 'nullable|string',
            'components.*.nama_komponen' => 'required|string|max:255',
            'components.*.tipe_kategori' => 'required|in:PENDAPATAN,BIAYA_OPERASIONAL',
            'components.*.yearly_data' => 'nullable|array',
            'components.*.yearly_data.*' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($project, $request) {
                $existing = $project->plComponents()->get();
                foreach($existing as $comp) {
                    $comp->yearlyData()->delete();
                }
                $project->plComponents()->delete();

                $components = $request->input('components', []);
                $idMap = [];
                $pending = $components;

                $saveYearlyData = function($model, $yearlyData) {
                    if (!empty($yearlyData)) {
                        foreach ($yearlyData as $tahunKe => $nilai) {
                            if (is_numeric($nilai) && $nilai > 0) {
                                $model->yearlyData()->create([
                                    'tahun_ke' => (int) $tahunKe,
                                    'nilai' => $nilai,
                                ]);
                            }
                        }
                    }
                };

                for ($i = 0; $i < 3; $i++) {
                    $nextPending = [];
                    foreach ($pending as $c) {
                        if (empty($c['parent_temp_id'])) {
                            $new = $project->plComponents()->create([
                                'nama_komponen' => $c['nama_komponen'],
                                'tipe_kategori' => $c['tipe_kategori'],
                            ]);
                            $idMap[$c['temp_id']] = $new->id;
                            $saveYearlyData($new, $c['yearly_data'] ?? []);
                        } 
                        else if (isset($idMap[$c['parent_temp_id']])) {
                            $new = $project->plComponents()->create([
                                'parent_id' => $idMap[$c['parent_temp_id']],
                                'nama_komponen' => $c['nama_komponen'],
                                'tipe_kategori' => $c['tipe_kategori'],
                            ]);
                            $idMap[$c['temp_id']] = $new->id;
                            $saveYearlyData($new, $c['yearly_data'] ?? []);
                        } 
                        else {
                            $nextPending[] = $c;
                        }
                    }
                    $pending = $nextPending;
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Data Proyeksi Laba Rugi berhasil disimpan!',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data Laba Rugi: ' . $e->getMessage(),
            ], 500);
        }
    }
}
