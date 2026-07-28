<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Kabupaten;
use App\Models\Sektor;
use App\Models\Lokasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    /**
     * Dashboard Executive Peluang Investasi (IPRO Engine).
     */
    public function dashboard()
    {
        $user = Auth::user();
        $projects = $user->projects()
            ->with(['kabupaten', 'sektor', 'lokasi', 'capexComponents', 'plComponents'])
            ->latest()
            ->get();

        $recentProjects = $projects->take(5);

        $kabupatens = Kabupaten::orderBy('nama_kabupaten')->get();
        $sektors = Sektor::orderBy('nama_sektor')->get();
        $lokasis = Lokasi::orderBy('nama')->get();

        return view('operator.peluang_investasi.dashboard', compact('projects', 'recentProjects', 'kabupatens', 'sektors', 'lokasis'));
    }

    /**
     * Menampilkan daftar proyek investasi dalam format Tabel Memanjang (Wide Table Row).
     */
    public function index()
    {
        $user = Auth::user();
        $projects = $user->projects()
            ->with(['kabupaten', 'sektor', 'lokasi', 'capexComponents', 'plComponents'])
            ->latest()
            ->get();

        $kabupatens = Kabupaten::orderBy('nama_kabupaten')->get();
        $sektors = Sektor::orderBy('nama_sektor')->get();
        $lokasis = Lokasi::orderBy('nama')->get();

        return view('operator.peluang_investasi.projects.index', compact('projects', 'kabupatens', 'sektors', 'lokasis'));
    }

    /**
     * Menyimpan proyek investasi baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_proyek' => 'required|string|max:255',
            'kabupaten_id' => 'nullable|exists:kabupaten,kab_id',
            'sektor_id' => 'nullable|exists:sektor,sektor_id',
            'lokasi_id' => 'nullable|exists:lokasi,id',
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
        
        $project->load(['kabupaten', 'sektor', 'lokasi', 'capexComponents', 'plComponents']);
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
            'sektor_id' => 'nullable|exists:sektor,sektor_id',
            'lokasi_id' => 'nullable|exists:lokasi,id',
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
