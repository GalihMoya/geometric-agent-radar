<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Cabang;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AgentController extends Controller
{
    /**
     * Render the main radar map view.
     */
    public function index()
    {
        return view('radar');
    }

    /**
     * Return agents JSON list and radar statistics for Axios consumption.
     */
    public function getAgents(Request $request): JsonResponse
    {
        $query = Agent::with('cabang');

        // City / Cabang Filter
        if ($request->filled('city') && $request->city !== 'all') {
            $query->byCabang($request->city);
        } elseif ($request->filled('cabang_id') && $request->cabang_id !== 'all') {
            $query->byCabang($request->cabang_id);
        }

        // Status Filter (aktif/nonaktif)
        if ($request->filled('status') && $request->status !== 'all') {
            $query->byStatus($request->status);
        }

        // Type Filter (tipe_agen)
        if ($request->filled('type') && $request->type !== 'all') {
            $query->byTipe($request->type);
        } elseif ($request->filled('tipe_agen') && $request->tipe_agen !== 'all') {
            $query->byTipe($request->tipe_agen);
        }

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_agen', 'like', "%{$search}%")
                  ->orWhere('nama_pemilik', 'like', "%{$search}%")
                  ->orWhere('alamat_lengkap', 'like', "%{$search}%")
                  ->orWhere('nomor_whatsapp', 'like', "%{$search}%")
                  ->orWhere('tipe_agen', 'like', "%{$search}%");
            });
        }

        $agents = $query->orderBy('id', 'asc')->get()->map(function ($agent) {
            // Add helper property for frontend compatibility
            $agent->city = $agent->cabang ? $agent->cabang->kode_cabang : 'tulungagung';
            return $agent;
        });

        // Base or isolated stats query
        $statsQuery = Agent::query();
        if ($request->filled('city') && $request->city !== 'all') {
            $statsQuery->byCabang($request->city);
        }

        // Calculate statistics
        $stats = [
            'total' => $statsQuery->count(),
            'aktif' => (clone $statsQuery)->where('status', 'aktif')->count(),
            'nonaktif' => (clone $statsQuery)->where('status', 'nonaktif')->count(),
            'cities' => [
                'tulungagung' => Agent::whereHas('cabang', fn($q) => $q->where('kode_cabang', 'tulungagung'))->count(),
                'blitar' => Agent::whereHas('cabang', fn($q) => $q->where('kode_cabang', 'blitar'))->count(),
                'trenggalek' => Agent::whereHas('cabang', fn($q) => $q->where('kode_cabang', 'trenggalek'))->count(),
            ]
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'count' => $agents->count(),
            'data' => $agents,
        ]);
    }

    /**
     * Return list of 3 main Radar Headquarters / Branch Offices (Tulungagung, Blitar, Trenggalek).
     */
    public function getHqLocations(): JsonResponse
    {
        $hqs = [
            [
                'id' => 'hq-tulungagung',
                'name' => 'Kantor Radar Tulungagung (HQ Utama)',
                'city' => 'tulungagung',
                'city_label' => 'Tulungagung',
                'address' => 'Jl. I Gusti Ngurah Rai No. 34 / Jl. Jayeng Kusuma, Bago, Kec. Tulungagung',
                'phone' => '(0355) 321888',
                'color' => '#004B87',
                'accent' => '#0073E6',
                'marker_class' => 'hq-marker-tulungagung',
                'latitude' => -8.059625,
                'longitude' => 111.9071825,
                'description' => 'Kantor Pusat Biro Jawa Pos Radar Tulungagung. Pusat komando sirkulasi, percetakan, dan distribusi agen koran regional.',
            ],
            [
                'id' => 'hq-blitar',
                'name' => 'Kantor Radar Blitar',
                'city' => 'blitar',
                'city_label' => 'Blitar',
                'address' => 'Jl. Mastrip No. 12 / Jl. Kenanga, Kepanjenkidul, Kota Blitar',
                'phone' => '(0342) 811234',
                'color' => '#D90429',
                'accent' => '#EF233C',
                'marker_class' => 'hq-marker-blitar',
                'latitude' => -8.0931251,
                'longitude' => 112.1789795,
                'description' => 'Biro Radar Blitar. Pos sirkulasi dan distribusi koran wilayah Kota dan Kabupaten Blitar.',
            ],
            [
                'id' => 'hq-trenggalek',
                'name' => 'Kantor Radar Trenggalek',
                'city' => 'trenggalek',
                'city_label' => 'Trenggalek',
                'address' => 'Jl. Panglima Sudirman / Jl. Brigjen Soetran No. 05, Trenggalek',
                'phone' => '(0355) 791456',
                'color' => '#E5A900',
                'accent' => '#FFC107',
                'marker_class' => 'hq-marker-trenggalek',
                'latitude' => -8.0669383,
                'longitude' => 111.70837,
                'description' => 'Biro Radar Trenggalek. Pusat layanan pelanggan dan distribusi agen koran pesisir & pegunungan Trenggalek.',
            ],
        ];

        return response()->json([
            'success' => true,
            'count' => count($hqs),
            'data' => $hqs,
        ]);
    }
}
