<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAgentCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('role', 'admin')->first();
    }

    /**
     * Test admin can view agents index page.
     */
    public function test_admin_can_view_agents_index_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.agents.index'));

        $response->assertStatus(200);
        $response->assertSee('Data Agen Spasial Mataraman');
        $response->assertSee('Tambah Agen Baru');
    }

    /**
     * Test admin can filter agents by city on index page.
     */
    public function test_admin_can_filter_agents_by_city(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.agents.index', ['city' => 'tulungagung']));

        $response->assertStatus(200);
        $response->assertViewHas('agents');
    }

    /**
     * Test admin can view create agent page.
     */
    public function test_admin_can_view_create_agent_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.agents.create'));

        $response->assertStatus(200);
        $response->assertSee('Pendaftaran Agen Spasial Baru');
        $response->assertSee('Titik Koordinat Geospatial');
        $response->assertSee('admin-map');
    }

    /**
     * Test admin can store new agent with valid coordinates and details.
     */
    public function test_admin_can_store_new_agent(): void
    {
        $data = [
            'code' => 'TA-999',
            'name' => 'Bambang Sudarsono',
            'city' => 'tulungagung',
            'district' => 'Kedungwaru',
            'village' => 'Rejotangan',
            'type' => 'Field Reporter',
            'status' => 'active',
            'latitude' => -8.064500,
            'longitude' => 111.902500,
            'signal_strength' => 90,
            'phone' => '+62 812-3456-7890',
            'description' => 'Agen peliputan khusus investigasi wilayah Kedungwaru.',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.agents.store'), $data);

        $response->assertRedirect(route('admin.agents.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('agents', [
            'code' => 'TA-999',
            'name' => 'Bambang Sudarsono',
            'city' => 'tulungagung',
        ]);
    }

    /**
     * Test store validation fails when required fields or coordinates are missing.
     */
    public function test_store_agent_validation_fails_on_missing_required_fields(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.agents.store'), [
            'name' => '',
            'code' => '',
            'latitude' => 'invalid-lat',
        ]);

        $response->assertSessionHasErrors(['code', 'name', 'city', 'type', 'status', 'latitude', 'longitude', 'signal_strength']);
    }

    /**
     * Test store validation fails when agent code is duplicate.
     */
    public function test_store_agent_validation_fails_on_duplicate_code(): void
    {
        $existingAgent = Agent::first();

        $response = $this->actingAs($this->admin)->post(route('admin.agents.store'), [
            'code' => $existingAgent->code,
            'name' => 'Agent Duplicate',
            'city' => 'tulungagung',
            'type' => 'Field Reporter',
            'status' => 'active',
            'latitude' => -8.0645,
            'longitude' => 111.9025,
            'signal_strength' => 85,
        ]);

        $response->assertSessionHasErrors('code');
    }

    /**
     * Test admin can view edit page.
     */
    public function test_admin_can_view_edit_agent_page(): void
    {
        $agent = Agent::first();

        $response = $this->actingAs($this->admin)->get(route('admin.agents.edit', $agent));

        $response->assertStatus(200);
        $response->assertSee($agent->name);
        $response->assertSee($agent->code);
        $response->assertSee('admin-map');
    }

    /**
     * Test admin can update agent data and coordinates.
     */
    public function test_admin_can_update_agent(): void
    {
        $agent = Agent::first();

        $updateData = [
            'code' => $agent->code,
            'name' => 'Nama Agen Terupdate',
            'city' => 'blitar',
            'district' => 'Kepanjenkidul',
            'village' => 'Bendo',
            'type' => 'Intelijen Spasial',
            'status' => 'patrol',
            'latitude' => -8.098300,
            'longitude' => 112.168100,
            'signal_strength' => 95,
            'phone' => '+62 813-9999-8888',
            'description' => 'Deskripsi tugas terupdate.',
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.agents.update', $agent), $updateData);

        $response->assertRedirect(route('admin.agents.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('agents', [
            'id' => $agent->id,
            'name' => 'Nama Agen Terupdate',
            'city' => 'blitar',
            'status' => 'patrol',
        ]);
    }

    /**
     * Test admin can delete agent.
     */
    public function test_admin_can_delete_agent(): void
    {
        $agent = Agent::create([
            'code' => 'TEMP-001',
            'name' => 'Agent To Delete',
            'city' => 'trenggalek',
            'type' => 'Field Reporter',
            'status' => 'standby',
            'latitude' => -8.0506,
            'longitude' => 111.7145,
            'signal_strength' => 70,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.agents.destroy', $agent));

        $response->assertRedirect(route('admin.agents.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('agents', [
            'id' => $agent->id,
            'code' => 'TEMP-001',
        ]);
    }
}
