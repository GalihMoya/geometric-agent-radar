<?php

namespace Tests\Feature;

use App\Models\Agent;
use Tests\TestCase;

class RadarMataramanTest extends TestCase
{
    /**
     * Test the radar index page returns status 200 and contains Radar Tulungagung branding & modals.
     */
    public function test_radar_index_page_returns_successful_response_with_tulungagung_theme(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('JAWA POS RADAR TULUNGAGUNG');
        $response->assertSee('BIRO MATARAMAN');
        $response->assertSee('agentDetailModal');
        $response->assertSee('hqDetailModal');
        $response->assertSee('Tulungagung (Biru)');
        $response->assertSee('Blitar (Merah)');
        $response->assertSee('Trenggalek (Kuning)');
    }

    /**
     * Test /api/hq returns the 3 Radar Headquarters (Tulungagung, Blitar, Trenggalek).
     */
    public function test_api_hq_returns_3_radar_headquarters(): void
    {
        $response = $this->getJson('/api/hq');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'count' => 3,
        ]);

        $data = $response->json('data');
        $this->assertCount(3, $data);

        $cities = array_column($data, 'city');
        $this->assertContains('tulungagung', $cities);
        $this->assertContains('blitar', $cities);
        $this->assertContains('trenggalek', $cities);

        // Verify Tulungagung HQ coords
        $taHq = collect($data)->firstWhere('city', 'tulungagung');
        $this->assertEquals(-8.0645, $taHq['latitude']);
        $this->assertEquals(111.9025, $taHq['longitude']);
    }

    /**
     * Test /api/agents returns all Mataraman agents and correct statistical breakdown.
     */
    public function test_api_agents_returns_mataraman_agents_and_statistics(): void
    {
        $response = $this->getJson('/api/agents');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'stats' => [
                'total',
                'active',
                'patrol',
                'alert',
                'standby',
                'avg_signal',
                'cities' => [
                    'tulungagung',
                    'blitar',
                    'trenggalek'
                ]
            ],
            'count',
            'data'
        ]);

        $this->assertGreaterThanOrEqual(15, $response->json('count'));
    }

    /**
     * Test /api/agents?city=tulungagung isolates and only returns Tulungagung agents.
     */
    public function test_api_agents_filtering_by_city_tulungagung(): void
    {
        $response = $this->getJson('/api/agents?city=tulungagung');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotEmpty($data);

        foreach ($data as $agent) {
            $this->assertEquals('tulungagung', $agent['city']);
            $this->assertStringStartsWith('AGT-TA-', $agent['code']);
        }
    }

    /**
     * Test /api/agents?city=blitar isolates and only returns Blitar agents.
     */
    public function test_api_agents_filtering_by_city_blitar(): void
    {
        $response = $this->getJson('/api/agents?city=blitar');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotEmpty($data);

        foreach ($data as $agent) {
            $this->assertEquals('blitar', $agent['city']);
            $this->assertStringStartsWith('AGT-BL-', $agent['code']);
        }
    }

    /**
     * Test /api/agents?city=trenggalek isolates and only returns Trenggalek agents.
     */
    public function test_api_agents_filtering_by_city_trenggalek(): void
    {
        $response = $this->getJson('/api/agents?city=trenggalek');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotEmpty($data);

        foreach ($data as $agent) {
            $this->assertEquals('trenggalek', $agent['city']);
            $this->assertStringStartsWith('AGT-TG-', $agent['code']);
        }
    }

    /**
     * Test /api/agents search by keyword.
     */
    public function test_api_agents_search(): void
    {
        $response = $this->getJson('/api/agents?search=Bambang');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertEquals('Bambang Pamungkas', $data[0]['name']);
        $this->assertEquals('tulungagung', $data[0]['city']);
    }

    /**
     * Test GeoJSON data files exist and are valid JSON.
     */
    public function test_geojson_data_files_exist_and_valid(): void
    {
        $regionsFile = public_path('data/geojson/mataraman_regions.json');
        $districtsFile = public_path('data/geojson/districts.json');
        $villagesFile = public_path('data/geojson/villages.json');
        $hqFile = public_path('data/geojson/radar_hq.json');

        $this->assertFileExists($regionsFile);
        $this->assertFileExists($districtsFile);
        $this->assertFileExists($villagesFile);
        $this->assertFileExists($hqFile);

        $this->assertNotNull(json_decode(file_get_contents($regionsFile)));
        $this->assertNotNull(json_decode(file_get_contents($districtsFile)));
        $this->assertNotNull(json_decode(file_get_contents($villagesFile)));
        $this->assertNotNull(json_decode(file_get_contents($hqFile)));
    }

    /**
     * Test GeoJSON contains correct centers and bounding boxes as specified in Issue #5.
     */
    public function test_geojson_contains_correct_mataraman_bounds_and_centers(): void
    {
        $regionsFile = public_path('data/geojson/mataraman_regions.json');
        $json = json_decode(file_get_contents($regionsFile), true);

        $features = collect($json['features'])->keyBy('properties.id');

        // Tulungagung
        $this->assertTrue($features->has('tulungagung'));
        $taProps = $features['tulungagung']['properties'];
        $this->assertEquals([-8.066, 111.900], $taProps['center']);
        $this->assertEquals([-8.350, 111.750], $taProps['bounding_box']['sw']);
        $this->assertEquals([-7.850, 112.100], $taProps['bounding_box']['ne']);

        // Blitar
        $this->assertTrue($features->has('blitar'));
        $blProps = $features['blitar']['properties'];
        $this->assertEquals([-8.100, 112.160], $blProps['center']);
        $this->assertEquals([-8.380, 112.000], $blProps['bounding_box']['sw']);
        $this->assertEquals([-7.800, 112.450], $blProps['bounding_box']['ne']);

        // Trenggalek
        $this->assertTrue($features->has('trenggalek'));
        $tgProps = $features['trenggalek']['properties'];
        $this->assertEquals([-8.050, 111.710], $tgProps['center']);
        $this->assertEquals([-8.450, 111.400], $tgProps['bounding_box']['sw']);
        $this->assertEquals([-7.850, 111.850], $tgProps['bounding_box']['ne']);
    }

    /**
     * Test all agents in database are strictly within their respective regional bounding boxes.
     */
    public function test_all_agents_coordinates_within_prescribed_regional_bounding_boxes(): void
    {
        $bounds = [
            'tulungagung' => [
                'min_lat' => -8.350,
                'max_lat' => -7.850,
                'min_lng' => 111.750,
                'max_lng' => 112.100,
            ],
            'blitar' => [
                'min_lat' => -8.380,
                'max_lat' => -7.800,
                'min_lng' => 112.000,
                'max_lng' => 112.450,
            ],
            'trenggalek' => [
                'min_lat' => -8.450,
                'max_lat' => -7.850,
                'min_lng' => 111.400,
                'max_lng' => 111.850,
            ],
        ];

        $agents = Agent::all();
        $this->assertGreaterThanOrEqual(15, $agents->count());

        foreach ($agents as $agent) {
            $city = $agent->city;
            $this->assertArrayHasKey($city, $bounds, "Agent {$agent->code} has invalid city: {$city}");

            $cityBound = $bounds[$city];
            $this->assertGreaterThanOrEqual(
                $cityBound['min_lat'],
                $agent->latitude,
                "Agent {$agent->code} ({$city}) latitude {$agent->latitude} is below SW min_lat {$cityBound['min_lat']}"
            );
            $this->assertLessThanOrEqual(
                $cityBound['max_lat'],
                $agent->latitude,
                "Agent {$agent->code} ({$city}) latitude {$agent->latitude} exceeds NE max_lat {$cityBound['max_lat']}"
            );

            $this->assertGreaterThanOrEqual(
                $cityBound['min_lng'],
                $agent->longitude,
                "Agent {$agent->code} ({$city}) longitude {$agent->longitude} is below SW min_lng {$cityBound['min_lng']}"
            );
            $this->assertLessThanOrEqual(
                $cityBound['max_lng'],
                $agent->longitude,
                "Agent {$agent->code} ({$city}) longitude {$agent->longitude} exceeds NE max_lng {$cityBound['max_lng']}"
            );
        }
    }
}
