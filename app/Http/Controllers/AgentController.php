<?php

namespace App\Http\Controllers;

use App\Models\Agent;
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
        $query = Agent::query();

        // City Isolation / Filter
        if ($request->filled('city') && $request->city !== 'all') {
            $query->byCity($request->city);
        }

        // Status Filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->byStatus($request->status);
        }

        // Type Filter
        if ($request->filled('type') && $request->type !== 'all') {
            $query->byType($request->type);
        }

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('district', 'like', "%{$search}%")
                  ->orWhere('village', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $agents = $query->orderBy('id', 'asc')->get();

        // Filter-specific or Base stats query
        $statsQuery = Agent::query();
        if ($request->filled('city') && $request->city !== 'all') {
            $statsQuery->where('city', $request->city);
        }

        // Calculate statistics
        $stats = [
            'total' => $statsQuery->count(),
            'active' => (clone $statsQuery)->where('status', 'active')->count(),
            'patrol' => (clone $statsQuery)->where('status', 'patrol')->count(),
            'alert' => (clone $statsQuery)->where('status', 'alert')->count(),
            'standby' => (clone $statsQuery)->where('status', 'standby')->count(),
            'avg_signal' => round((clone $statsQuery)->avg('signal_strength') ?? 0, 1),
            'cities' => [
                'tulungagung' => Agent::where('city', 'tulungagung')->count(),
                'blitar' => Agent::where('city', 'blitar')->count(),
                'trenggalek' => Agent::where('city', 'trenggalek')->count(),
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
     * Return list of 3 main Radar Headquarters (Tulungagung, Blitar, Trenggalek).
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
                'latitude' => -8.0645,
                'longitude' => 111.9025,
                'personnel' => 12,
                'description' => 'Kantor Pusat Biro Jawa Pos Radar Tulungagung. Pusat komando liputan, percetakan, dan radar taktis regional.',
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
                'latitude' => -8.0983,
                'longitude' => 112.1681,
                'personnel' => 9,
                'description' => 'Biro Radar Blitar. Pos liputan strategis Makam Bung Karno, sentra agro, dan dinamika Kota/Kabupaten Blitar.',
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
                'latitude' => -8.0506,
                'longitude' => 111.7145,
                'personnel' => 8,
                'description' => 'Biro Radar Trenggalek. Pusat informasi pesisir selatan Prigi, pegunungan Menak Sopal, dan infrastruktur wilayah.',
            ],
        ];

        return response()->json([
            'success' => true,
            'count' => count($hqs),
            'data' => $hqs,
        ]);
    }
}
