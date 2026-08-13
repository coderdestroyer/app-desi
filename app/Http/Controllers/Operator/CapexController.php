<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\CapexComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CapexController extends Controller
{
    public function index(Request $request, Project $project)
    {
        $this->authorizeProjectOwner($project);

        $components = $project->capexComponents()
            ->orderBy('id')
            ->get();

        if ($components->isEmpty()) {
            DB::transaction(function () use ($project) {
                $project->capexComponents()->create([
                    'nama_komponen' => 'Persiapan',
                ]);
                $project->capexComponents()->create([
                    'nama_komponen' => 'Fasilitas Service dan Pendukung',
                ]);
            });

            $components = $project->capexComponents()
                ->orderBy('id')
                ->get();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'components' => $components
            ]);
        }

        return view('operator.peluang_investasi.projects.capex', compact('project', 'components'));
    }

    /**
     * Menyimpan data estimasi CAPEX proyek.
     */
    public function store(Request $request, Project $project)
    {
        $this->authorizeProjectOwner($project);

        $request->validate([
            'components' => 'nullable|array',
            'components.*.temp_id' => 'required|string',
            'components.*.parent_temp_id' => 'nullable|string',
            'components.*.nama_komponen' => 'required|string|max:255',
            'components.*.volume' => 'nullable|numeric|min:0',
            'components.*.satuan' => 'nullable|string|max:50',
            'components.*.luas' => 'nullable|numeric|min:0',
            'components.*.harga_m2' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($project, $request) {
                $project->capexComponents()->delete();

                $components = $request->input('components', []);

                $parents = array_values(array_filter($components, function ($item) {
                    return empty($item['parent_temp_id']);
                }));

                // Enforce minimum 2 default parent categories if missing
                if (count($parents) < 1) {
                    $parents[] = ['temp_id' => 'def_1', 'nama_komponen' => 'Persiapan'];
                }
                if (count($parents) < 2) {
                    $parents[] = ['temp_id' => 'def_2', 'nama_komponen' => 'Fasilitas Service dan Pendukung'];
                }

                $children = array_filter($components, function ($item) {
                    return !empty($item['parent_temp_id']);
                });

                $orderedParents = [];
                if (count($parents) >= 2) {
                    $firstDefault = $parents[0];
                    $lastDefault = $parents[count($parents) - 1];
                    $middle = array_slice($parents, 1, count($parents) - 2);
                    $orderedParents[] = $firstDefault;
                    $orderedParents[] = $lastDefault;
                    foreach ($middle as $m) {
                        $orderedParents[] = $m;
                    }
                } else {
                    $orderedParents = $parents;
                }

                $idMap = [];

                foreach ($orderedParents as $p) {
                    $newParent = $project->capexComponents()->create([
                        'nama_komponen' => $p['nama_komponen'],
                        'volume' => null,
                        'satuan' => null,
                        'luas' => null,
                        'harga_m2' => null,
                    ]);
                    $idMap[$p['temp_id']] = $newParent->id;
                }

                foreach ($children as $c) {
                    $realParentId = $idMap[$c['parent_temp_id']] ?? null;

                    if ($realParentId) {
                        $project->capexComponents()->create([
                            'parent_id' => $realParentId,
                            'nama_komponen' => $c['nama_komponen'],
                            'volume' => ($c['volume'] !== '' && $c['volume'] !== null) ? $c['volume'] : null,
                            'satuan' => ($c['satuan'] !== '' && $c['satuan'] !== null) ? $c['satuan'] : null,
                            'luas' => ($c['luas'] !== '' && $c['luas'] !== null) ? $c['luas'] : null,
                            'harga_m2' => ($c['harga_m2'] !== '' && $c['harga_m2'] !== null) ? $c['harga_m2'] : null,
                        ]);
                    }
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Data estimasi CAPEX berhasil disimpan!',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data CAPEX: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function authorizeProjectOwner(Project $project): void
    {
        if ($project->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke proyek ini.');
        }
    }
}
