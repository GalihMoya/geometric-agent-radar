<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Cabang;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AgentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Agent::with('cabang');

        // Search filter
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama_agen', 'like', "%{$search}%")
                  ->orWhere('nama_pemilik', 'like', "%{$search}%")
                  ->orWhere('alamat_lengkap', 'like', "%{$search}%")
                  ->orWhere('nomor_whatsapp', 'like', "%{$search}%")
                  ->orWhere('tipe_agen', 'like', "%{$search}%");
            });
        }

        // Cabang / City filter
        if ($request->filled('cabang_id') && $request->cabang_id !== 'all') {
            $query->byCabang($request->cabang_id);
        } elseif ($request->filled('city') && $request->city !== 'all') {
            $query->byCabang($request->city);
        }

        // Status filter (aktif / nonaktif)
        if ($request->filled('status') && $request->status !== 'all') {
            $query->byStatus($request->status);
        }

        // Type filter
        if ($request->filled('tipe_agen') && $request->tipe_agen !== 'all') {
            $query->byTipe($request->tipe_agen);
        } elseif ($request->filled('type') && $request->type !== 'all') {
            $query->byTipe($request->type);
        }

        $agents = $query->latest()->paginate(10)->withQueryString();
        $cabangs = Cabang::all();

        // Statistics for dashboard widgets
        $stats = [
            'total' => Agent::count(),
            'aktif' => Agent::where('status', 'aktif')->count(),
            'nonaktif' => Agent::where('status', 'nonaktif')->count(),
            'tulungagung' => Agent::whereHas('cabang', fn($q) => $q->where('kode_cabang', 'tulungagung'))->count(),
            'blitar' => Agent::whereHas('cabang', fn($q) => $q->where('kode_cabang', 'blitar'))->count(),
            'trenggalek' => Agent::whereHas('cabang', fn($q) => $q->where('kode_cabang', 'trenggalek'))->count(),
        ];

        // Unique types for filter dropdown
        $types = Agent::distinct()->pluck('tipe_agen')->filter()->values();

        return view('admin.agents.index', compact('agents', 'stats', 'types', 'cabangs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $cabangs = Cabang::all();
        return view('admin.agents.create', compact('cabangs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_agen' => ['required', 'string', 'max:255'],
            'nama_pemilik' => ['required', 'string', 'max:255'],
            'cabang_id' => ['required', 'integer', 'exists:cabangs,id'],
            'tipe_agen' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in(['aktif', 'nonaktif'])],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'nomor_whatsapp' => ['nullable', 'string', 'max:50'],
            'alamat_lengkap' => ['nullable', 'string'],
        ], [
            'nama_agen.required' => 'Nama kios / agen wajib diisi.',
            'nama_pemilik.required' => 'Nama pemilik wajib diisi.',
            'cabang_id.required' => 'Kantor cabang Radar wajib dipilih.',
            'cabang_id.exists' => 'Cabang yang dipilih tidak valid.',
            'tipe_agen.required' => 'Tipe mitra wajib dipilih.',
            'status.required' => 'Status agen wajib dipilih.',
            'latitude.required' => 'Koordinat Latitude wajib diisi.',
            'latitude.numeric' => 'Latitude harus berupa angka valid.',
            'longitude.required' => 'Koordinat Longitude wajib diisi.',
            'longitude.numeric' => 'Longitude harus berupa angka valid.',
        ]);

        $agent = Agent::create($validated);

        return redirect()->route('admin.agents.index')
            ->with('success', "Kios / Agen {$agent->nama_agen} ({$agent->nama_pemilik}) berhasil ditambahkan!");
    }

    /**
     * Display the specified resource.
     */
    public function show(Agent $agent)
    {
        return redirect()->route('admin.agents.edit', $agent);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Agent $agent)
    {
        $cabangs = Cabang::all();
        return view('admin.agents.edit', compact('agent', 'cabangs'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Agent $agent)
    {
        $validated = $request->validate([
            'nama_agen' => ['required', 'string', 'max:255'],
            'nama_pemilik' => ['required', 'string', 'max:255'],
            'cabang_id' => ['required', 'integer', 'exists:cabangs,id'],
            'tipe_agen' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in(['aktif', 'nonaktif'])],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'nomor_whatsapp' => ['nullable', 'string', 'max:50'],
            'alamat_lengkap' => ['nullable', 'string'],
        ], [
            'nama_agen.required' => 'Nama kios / agen wajib diisi.',
            'nama_pemilik.required' => 'Nama pemilik wajib diisi.',
            'cabang_id.required' => 'Kantor cabang Radar wajib dipilih.',
            'cabang_id.exists' => 'Cabang yang dipilih tidak valid.',
            'tipe_agen.required' => 'Tipe mitra wajib dipilih.',
            'status.required' => 'Status agen wajib dipilih.',
            'latitude.required' => 'Koordinat Latitude wajib diisi.',
            'latitude.numeric' => 'Latitude harus berupa angka valid.',
            'longitude.required' => 'Koordinat Longitude wajib diisi.',
            'longitude.numeric' => 'Longitude harus berupa angka valid.',
        ]);

        $agent->update($validated);

        return redirect()->route('admin.agents.index')
            ->with('success', "Data agen {$agent->nama_agen} ({$agent->nama_pemilik}) berhasil diperbarui!");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Agent $agent)
    {
        $nama = $agent->nama_agen;
        $pemilik = $agent->nama_pemilik;
        $agent->delete();

        return redirect()->route('admin.agents.index')
            ->with('success', "Data agen {$nama} ({$pemilik}) telah berhasil dihapus dari sistem.");
    }
}
