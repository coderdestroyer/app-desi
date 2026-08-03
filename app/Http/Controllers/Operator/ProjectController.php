<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Kabupaten;
use App\Models\Sektor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class ProjectController extends Controller
{
    /**
     * Dashboard Executive Peluang Investasi (IPRO Engine).
     */
    public function dashboard()
    {
        $user = Auth::user();
        $projects = $user->projects()
            ->with(['kabupaten', 'kecamatan', 'sektor', 'capexComponents', 'plComponents'])
            ->latest()
            ->get();

        $recentProjects = $projects->take(5);

        $kabupatens = $this->getAuthorizedKabupatens($user);
        $sektors = Sektor::orderBy('nama_sektor')->get();

        return view('operator.peluang_investasi.dashboard', compact('projects', 'recentProjects', 'kabupatens', 'sektors'));
    }

    /**
     * Menampilkan daftar proyek investasi dalam format Tabel Memanjang (Wide Table Row).
     */
    public function index()
    {
        $user = Auth::user();
        $projects = $user->projects()
            ->with(['kabupaten', 'kecamatan', 'sektor', 'capexComponents', 'plComponents'])
            ->latest()
            ->get();

        $kabupatens = $this->getAuthorizedKabupatens($user);
        $sektors = Sektor::orderBy('nama_sektor')->get();

        return view('operator.peluang_investasi.projects.index', compact('projects', 'kabupatens', 'sektors'));
    }

    /**
     * Mengambil daftar Kabupaten/Kota yang berhak diakses oleh user berdasarkan Regional Scope.
     */
    private function getAuthorizedKabupatens($user)
    {
        if ($user->isAdmin()) {
            return Kabupaten::orderBy('nama_kabupaten')->get();
        }

        if (!Schema::hasTable('user_wilayah_scopes')) {
            return Kabupaten::orderBy('nama_kabupaten')->get();
        }

        $scopes = $user->wilayahScopes()->get();

        if ($scopes->isEmpty()) {
            return Kabupaten::orderBy('nama_kabupaten')->get();
        }

        $query = Kabupaten::query();

        $query->where(function ($q) use ($scopes) {
            foreach ($scopes as $scope) {
                if ($scope->kabupaten_id) {
                    $q->orWhere('kab_id', $scope->kabupaten_id);
                } elseif ($scope->provinsi_id) {
                    $q->orWhere('provinsi_id', $scope->provinsi_id);
                }
            }
        });

        return $query->orderBy('nama_kabupaten')->get();
    }

    /**
     * Menyimpan proyek investasi baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_proyek' => 'required|string|max:255',
            'kabupaten_id' => 'nullable|exists:kabupaten,kab_id',
            'kecamatan_id' => 'nullable|exists:kecamatan,id',
            'sektor_id' => 'nullable|exists:sektor,sektor_id',
            'alamat_lokasi' => 'nullable|string',
            'deskripsi' => 'nullable|string',
            'tahun_awal' => 'required|integer|min:2000|max:2100',
            'jangka_waktu_tahun' => 'required|integer|min:1|max:50',
            'status_publikasi' => 'nullable|string|in:draft,published',
        ]);

        $validated['status_publikasi'] = $validated['status_publikasi'] ?? 'published';

        Auth::user()->projects()->create($validated);

        return redirect()->route('operator.projects.index')
            ->with('success', 'Proyek investasi IPRO berhasil dibuat!');
    }

    /**
     * Menampilkan detail proyek investasi & modul kalkulasi.
     */
    public function show(Project $project)
    {
        $this->authorizeProjectOwner($project);
        
        $project->load(['kabupaten', 'kecamatan', 'sektor', 'capexComponents', 'plComponents']);
        return view('operator.peluang_investasi.projects.show', compact('project'));
    }

    /**
     * Memperbarui data proyek investasi.
     */
    public function update(Request $request, Project $project)
    {
        $this->authorizeProjectOwner($project);

        $validated = $request->validate([
            'nama_proyek' => 'required|string|max:255',
            'kabupaten_id' => 'nullable|exists:kabupaten,kab_id',
            'kecamatan_id' => 'nullable|exists:kecamatan,id',
            'sektor_id' => 'nullable|exists:sektor,sektor_id',
            'alamat_lokasi' => 'nullable|string',
            'deskripsi' => 'nullable|string',
            'tahun_awal' => 'required|integer|min:2000|max:2100',
            'jangka_waktu_tahun' => 'required|integer|min:1|max:50',
            'status_publikasi' => 'nullable|string|in:draft,published',
        ]);

        $project->update($validated);

        return redirect()->route('operator.projects.index')
            ->with('success', 'Proyek investasi berhasil diperbarui!');
    }

    /**
     * Menghapus proyek investasi.
     */
    public function destroy(Project $project)
    {
        $this->authorizeProjectOwner($project);

        $project->delete();

        return redirect()->route('operator.projects.index')
            ->with('success', 'Proyek investasi berhasil dihapus!');
    }

    /**
     * Memastikan proyek milik pengguna yang sedang login.
     */
    private function authorizeProjectOwner(Project $project): void
    {
        if ($project->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke proyek ini.');
        }
    }
}
