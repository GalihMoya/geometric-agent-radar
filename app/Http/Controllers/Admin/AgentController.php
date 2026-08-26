<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AgentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Agent::query();

        // Search filter
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('district', 'like', "%{$search}%")
                  ->orWhere('village', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            });
        }

        // City filter
        if ($request->filled('city') && $request->city !== 'all') {
            $query->where('city', strtolower($request->city));
        }

        // Status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', strtolower($request->status));
        }

        // Type filter
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        $agents = $query->latest()->paginate(10)->withQueryString();

        // Statistics for dashboard widgets
        $stats = [
            'total' => Agent::count(),
            'active' => Agent::where('status', 'active')->count(),
            'patrol' => Agent::where('status', 'patrol')->count(),
            'alert' => Agent::where('status', 'alert')->count(),
            'standby' => Agent::where('status', 'standby')->count(),
            'tulungagung' => Agent::where('city', 'tulungagung')->count(),
            'blitar' => Agent::where('city', 'blitar')->count(),
            'trenggalek' => Agent::where('city', 'trenggalek')->count(),
        ];

        // Unique types for filter dropdown
        $types = Agent::distinct()->pluck('type')->filter()->values();

        return view('admin.agents.index', compact('agents', 'stats', 'types'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.agents.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:agents,code'],
            'name' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', Rule::in(['tulungagung', 'blitar', 'trenggalek'])],
            'district' => ['nullable', 'string', 'max:255'],
            'village' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in(['active', 'patrol', 'alert', 'standby'])],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'signal_strength' => ['required', 'integer', 'between:0,100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ], [
            'code.required' => 'Kode agen wajib diisi.',
            'code.unique' => 'Kode agen sudah digunakan, gunakan kode lain.',
            'name.required' => 'Nama agen wajib diisi.',
            'city.required' => 'Wilayah kabupaten/kota wajib dipilih.',
            'city.in' => 'Wilayah harus salah satu dari: Tulungagung, Blitar, atau Trenggalek.',
            'status.required' => 'Status operasional wajib dipilih.',
            'latitude.required' => 'Koordinat Latitude wajib diisi (bisa pilih dari peta atau ketik manual).',
            'latitude.numeric' => 'Latitude harus berupa angka valid.',
            'longitude.required' => 'Koordinat Longitude wajib diisi (bisa pilih dari peta atau ketik manual).',
            'longitude.numeric' => 'Longitude harus berupa angka valid.',
            'signal_strength.required' => 'Kekuatan sinyal wajib diisi (0 - 100%).',
        ]);

        $agent = Agent::create($validated);

        return redirect()->route('admin.agents.index')
            ->with('success', "Agen {$agent->name} ({$agent->code}) berhasil ditambahkan!");
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
        return view('admin.agents.edit', compact('agent'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Agent $agent)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('agents', 'code')->ignore($agent->id)],
            'name' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', Rule::in(['tulungagung', 'blitar', 'trenggalek'])],
            'district' => ['nullable', 'string', 'max:255'],
            'village' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in(['active', 'patrol', 'alert', 'standby'])],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'signal_strength' => ['required', 'integer', 'between:0,100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ], [
            'code.required' => 'Kode agen wajib diisi.',
            'code.unique' => 'Kode agen sudah digunakan oleh agen lain.',
            'name.required' => 'Nama agen wajib diisi.',
            'city.required' => 'Wilayah kabupaten/kota wajib dipilih.',
            'latitude.required' => 'Koordinat Latitude wajib diisi.',
            'latitude.numeric' => 'Latitude harus berupa angka valid.',
            'longitude.required' => 'Koordinat Longitude wajib diisi.',
            'longitude.numeric' => 'Longitude harus berupa angka valid.',
            'signal_strength.required' => 'Kekuatan sinyal wajib diisi (0 - 100%).',
        ]);

        $agent->update($validated);

        return redirect()->route('admin.agents.index')
            ->with('success', "Data agen {$agent->name} ({$agent->code}) berhasil diperbarui!");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Agent $agent)
    {
        $name = $agent->name;
        $code = $agent->code;
        $agent->delete();

        return redirect()->route('admin.agents.index')
            ->with('success', "Agen {$name} ({$code}) telah berhasil dihapus dari sistem.");
    }
}
