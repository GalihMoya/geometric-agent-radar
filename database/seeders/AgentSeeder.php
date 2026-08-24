<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AgentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $agents = [
            // Cluster Jakarta Central & Monas
            [
                'code' => 'AGT-NX01',
                'name' => 'Nexus Alpha',
                'type' => 'Tactical Lead',
                'status' => 'active',
                'latitude' => -6.1753924,
                'longitude' => 106.8271528,
                'signal_strength' => 98,
                'description' => 'Patrolling Monas Central Perimeter with high-frequency LiDAR radar scan.',
            ],
            [
                'code' => 'AGT-NX02',
                'name' => 'Vanguard Delta',
                'type' => 'Scout',
                'status' => 'active',
                'latitude' => -6.1805120,
                'longitude' => 106.8234500,
                'signal_strength' => 91,
                'description' => 'Thamrin corridor surveillance drone with active optical tracking.',
            ],
            [
                'code' => 'AGT-NX03',
                'name' => 'Shadow Recon',
                'type' => 'Recon',
                'status' => 'alert',
                'latitude' => -6.1712000,
                'longitude' => 106.8320000,
                'signal_strength' => 74,
                'description' => 'Harmoni sector - anomalous geometric radio signature detected.',
            ],
            [
                'code' => 'AGT-NX04',
                'name' => 'Cyber Sentinel',
                'type' => 'Interceptor',
                'status' => 'patrol',
                'latitude' => -6.1789000,
                'longitude' => 106.8305000,
                'signal_strength' => 88,
                'description' => 'Gambir transport hub perimeter sweep in progress.',
            ],

            // Cluster Jakarta South (SCBD & Senayan)
            [
                'code' => 'AGT-SC01',
                'name' => 'Aegis Guardian',
                'type' => 'Tactical Lead',
                'status' => 'active',
                'latitude' => -6.2255880,
                'longitude' => 106.8097460,
                'signal_strength' => 99,
                'description' => 'SCBD Core station gateway security sync.',
            ],
            [
                'code' => 'AGT-SC02',
                'name' => 'Ghost Runner',
                'type' => 'Scout',
                'status' => 'patrol',
                'latitude' => -6.2291000,
                'longitude' => 106.8035000,
                'signal_strength' => 85,
                'description' => 'Senayan sports complex perimeter radar node.',
            ],
            [
                'code' => 'AGT-SC03',
                'name' => 'Pulse Tracer',
                'type' => 'Interceptor',
                'status' => 'standby',
                'latitude' => -6.2214000,
                'longitude' => 106.8142000,
                'signal_strength' => 62,
                'description' => 'Gatot Subroto transit beacon standby mode for recharge.',
            ],
            [
                'code' => 'AGT-SC04',
                'name' => 'Specter 9',
                'type' => 'Recon',
                'status' => 'alert',
                'latitude' => -6.2330000,
                'longitude' => 106.8180000,
                'signal_strength' => 81,
                'description' => 'Kuningan area telemetry sensor alert: interference pattern.',
            ],

            // Cluster Bandung
            [
                'code' => 'AGT-BD01',
                'name' => 'Gedung Sate Sentinel',
                'type' => 'Tactical Lead',
                'status' => 'active',
                'latitude' => -6.9024840,
                'longitude' => 107.6186800,
                'signal_strength' => 95,
                'description' => 'Bandung Command Base relaying geo-spatial coordinates.',
            ],
            [
                'code' => 'AGT-BD02',
                'name' => 'Dago Ridge Drone',
                'type' => 'Scout',
                'status' => 'patrol',
                'latitude' => -6.8790000,
                'longitude' => 107.6150000,
                'signal_strength' => 90,
                'description' => 'Northern elevated radar grid scan covering Dago valley.',
            ],
            [
                'code' => 'AGT-BD03',
                'name' => 'Braga Phantom',
                'type' => 'Recon',
                'status' => 'active',
                'latitude' => -6.9175000,
                'longitude' => 107.6095000,
                'signal_strength' => 87,
                'description' => 'Historic city sector acoustic & seismic probe active.',
            ],

            // Cluster Yogyakarta
            [
                'code' => 'AGT-YK01',
                'name' => 'Malioboro Guardian',
                'type' => 'Tactical Lead',
                'status' => 'active',
                'latitude' => -7.7925000,
                'longitude' => 110.3658000,
                'signal_strength' => 96,
                'description' => 'Yogyakarta central meridian link node.',
            ],
            [
                'code' => 'AGT-YK02',
                'name' => 'Kraton Shield',
                'type' => 'Interceptor',
                'status' => 'active',
                'latitude' => -7.8052840,
                'longitude' => 110.3642030,
                'signal_strength' => 93,
                'description' => 'Cultural zone geomagnetic sensor array.',
            ],
            [
                'code' => 'AGT-YK03',
                'name' => 'Merapi Watcher',
                'type' => 'Recon',
                'status' => 'alert',
                'latitude' => -7.5407000,
                'longitude' => 110.4460000,
                'signal_strength' => 78,
                'description' => 'High-altitude volcanic thermal and atmospheric radar.',
            ],

            // Cluster Surabaya
            [
                'code' => 'AGT-SB01',
                'name' => 'Suramadu Sentry',
                'type' => 'Tactical Lead',
                'status' => 'active',
                'latitude' => -7.1990000,
                'longitude' => 112.7800000,
                'signal_strength' => 97,
                'description' => 'Bridge transit channel coastal radar lock.',
            ],
            [
                'code' => 'AGT-SB02',
                'name' => 'Tunjungan Eye',
                'type' => 'Scout',
                'status' => 'patrol',
                'latitude' => -7.2619000,
                'longitude' => 112.7388000,
                'signal_strength' => 89,
                'description' => 'Surabaya downtown commercial zone grid monitor.',
            ],
            [
                'code' => 'AGT-SB03',
                'name' => 'Tanjung Perak Beacon',
                'type' => 'Interceptor',
                'status' => 'standby',
                'latitude' => -7.2050000,
                'longitude' => 112.7330000,
                'signal_strength' => 67,
                'description' => 'Maritime dock radar node in low-power standby.',
            ],

            // Cluster Bali (Denpasar & Nusa Dua)
            [
                'code' => 'AGT-BL01',
                'name' => 'Nusa Dua Shield',
                'type' => 'Tactical Lead',
                'status' => 'active',
                'latitude' => -8.7950000,
                'longitude' => 115.2280000,
                'signal_strength' => 99,
                'description' => 'Southern maritime perimeter optical tracking radar.',
            ],
            [
                'code' => 'AGT-BL02',
                'name' => 'Uluwatu Beacon',
                'type' => 'Recon',
                'status' => 'alert',
                'latitude' => -8.8290000,
                'longitude' => 115.0840000,
                'signal_strength' => 84,
                'description' => 'Cliff-side radar picking up deep ocean acoustic waves.',
            ],
            [
                'code' => 'AGT-BL03',
                'name' => 'Kuta Wave Tracker',
                'type' => 'Scout',
                'status' => 'patrol',
                'latitude' => -8.7180000,
                'longitude' => 115.1680000,
                'signal_strength' => 92,
                'description' => 'Coastal tourism sector drone fleet coordinator.',
            ],

            // International Regional Nodes (Singapore, Tokyo)
            [
                'code' => 'AGT-SG01',
                'name' => 'Marina Orbital Node',
                'type' => 'Tactical Lead',
                'status' => 'active',
                'latitude' => 1.2838000,
                'longitude' => 103.8591000,
                'signal_strength' => 100,
                'description' => 'Southeast Asian primary telemetry uplink station.',
            ],
            [
                'code' => 'AGT-SG02',
                'name' => 'Changi Sector Drone',
                'type' => 'Interceptor',
                'status' => 'active',
                'latitude' => 1.3644000,
                'longitude' => 103.9915000,
                'signal_strength' => 95,
                'description' => 'Airspace security radar with automated vector detection.',
            ],
            [
                'code' => 'AGT-TK01',
                'name' => 'Shinjuku Cyber Nexus',
                'type' => 'Tactical Lead',
                'status' => 'active',
                'latitude' => 35.6900000,
                'longitude' => 139.6920000,
                'signal_strength' => 98,
                'description' => 'Tokyo hyper-dense metropolis electromagnetic scanner.',
            ],
            [
                'code' => 'AGT-TK02',
                'name' => 'Shibuya Grid Unit',
                'type' => 'Scout',
                'status' => 'patrol',
                'latitude' => 35.6595000,
                'longitude' => 139.7005000,
                'signal_strength' => 94,
                'description' => 'Pedestrian flow and geometric agent heat-map tracker.',
            ],
        ];

        foreach ($agents as $data) {
            \App\Models\Agent::updateOrCreate(
                ['code' => $data['code']],
                $data
            );
        }
    }
}
