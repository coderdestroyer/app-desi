<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Kabupaten;
use App\Models\Sektor;
use Illuminate\Http\Request;

class AdminProyekIproController extends Controller
{
    /**
     * Menampilkan daftar dokumen proyek IPRO yang diinput operator dari database.
     */
    public function index(Request $request)
    {
        $query = Project::with(['user', 'kabupaten', 'sektor', 'lokasi', 'capexComponents', 'plComponents'])
            ->latest();

        if ($request->filled('search')) {
            $search = strtolower(trim($request->search));
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(nama_proyek) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(deskripsi) LIKE ?', ["%{$search}%"]);
            });
        }

        if ($request->filled('kabupaten_id')) {
            $query->where('kabupaten_id', $request->kabupaten_id);
        }

        if ($request->filled('sektor_id')) {
            $query->where('sektor_id', $request->sektor_id);
        }

        $projects = $query->paginate(10)->withQueryString();
        $kabupatens = Kabupaten::orderBy('nama_kabupaten')->get();
        $sektors = Sektor::orderBy('nama_sektor')->get();

        return view('admin.proyek_ipro.index', compact('projects', 'kabupatens', 'sektors'));
    }

    /**
     * Menampilkan detail dokumen kelayakan IPRO (CAPEX, P&L, dan Arus Kas) dari database.
     */
    public function show($id)
    {
        $project = Project::with([
            'user', 
            'kabupaten', 
            'sektor', 
            'lokasi', 
            'capexComponents', 
            'plComponents.yearlyData'
        ])->findOrFail($id);

        // 1. Hitung total CAPEX riil
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

        // Kelompokkan Komponen CAPEX
        $capexParents = $project->capexComponents->where('parent_id', null);
        $capexList = [];
        foreach ($capexParents as $parent) {
            $items = $project->capexComponents->where('parent_id', $parent->id);
            $subtotal = 0;
            $itemList = [];
            foreach ($items as $item) {
                $v = $item->volume && $item->volume > 0 ? (float)$item->volume : 1;
                $l = $item->luas && $item->luas > 0 ? (float)$item->luas : 0;
                $h = $item->harga_m2 ? (float)$item->harga_m2 : 0;
                $tot = ($l > 0) ? ($v * $l * $h) : ($v * $h);
                $subtotal += $tot;

                $itemList[] = [
                    'nama' => $item->nama_komponen,
                    'vol' => $item->volume,
                    'satuan' => $item->satuan,
                    'luas' => $item->luas,
                    'harga_m2' => $item->harga_m2,
                    'total' => $tot,
                ];
            }

            $capexList[] = [
                'kategori' => $parent->nama_komponen,
                'subtotal' => $subtotal,
                'items' => $itemList,
            ];
        }

        // 2. Hitung Pendapatan & OPEX per Tahun untuk P&L dan Cashflow
        $pendapatanPerTahun = [];
        $opexPerTahun = [];

        for ($t = 1; $t <= $project->jangka_waktu_tahun; $t++) {
            $pendapatanPerTahun[$t] = 0;
            $opexPerTahun[$t] = 0;
        }

        foreach ($project->plComponents as $comp) {
            foreach ($comp->yearlyData as $yd) {
                $t = (int) $yd->tahun_ke;
                $val = (float) $yd->nilai;
                if ($t >= 1 && $t <= $project->jangka_waktu_tahun) {
                    if ($comp->tipe_kategori === 'PENDAPATAN') {
                        $pendapatanPerTahun[$t] += $val;
                    } else if ($comp->tipe_kategori === 'BIAYA_OPERASIONAL') {
                        $opexPerTahun[$t] += $val;
                    }
                }
            }
        }

        // Format Komponen P&L untuk Alpine.js Manager di Admin Read-Only View
        $plComponentsFormatted = $project->plComponents->map(function ($comp) {
            $yearlyMap = [];
            foreach ($comp->yearlyData as $yd) {
                $yearlyMap[$yd->tahun_ke] = (float) $yd->nilai;
            }
            return [
                'id' => $comp->id,
                'parent_id' => $comp->parent_id,
                'tipe_kategori' => $comp->tipe_kategori,
                'nama_komponen' => $comp->nama_komponen,
                'yearly_data' => $yearlyMap,
            ];
        });

        // 3. Parameter Keuangan & Rasio Modal
        $rasioEquity = (float)($project->rasio_modal_sendiri ?: 60);
        $rasioDebt = (float)($project->rasio_pinjaman_kredit ?: 40);
        $equityAmount = $totalCapex * ($rasioEquity / 100);
        $debtAmount = $totalCapex * ($rasioDebt / 100);

        return view('admin.proyek_ipro.show', compact(
            'project', 
            'totalCapex', 
            'capexList', 
            'pendapatanPerTahun',
            'opexPerTahun',
            'plComponentsFormatted',
            'equityAmount', 
            'debtAmount'
        ));
    }
}
