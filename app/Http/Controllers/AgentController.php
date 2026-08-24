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

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $agents = $query->orderBy('id', 'desc')->get();

        // Calculate statistics
        $stats = [
            'total' => Agent::count(),
            'active' => Agent::where('status', 'active')->count(),
            'patrol' => Agent::where('status', 'patrol')->count(),
            'alert' => Agent::where('status', 'alert')->count(),
            'standby' => Agent::where('status', 'standby')->count(),
            'avg_signal' => round(Agent::avg('signal_strength') ?? 0, 1),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'count' => $agents->count(),
            'data' => $agents,
        ]);
    }
}
