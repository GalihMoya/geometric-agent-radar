<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\Cabang;
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
        $response->assertSee('Data Agen & Kios Koran Radar', false);
        $response->assertSee('Tambah Agen Baru');
        $response->assertSee('Nama Kios & Pemilik', false);
        $response->assertSee('Tipe Mitra');
        $response->assertSee('Status agen');
    }

    /**
     * Test admin can filter agents by city/cabang on index page.
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
        $response->assertSee('Pendaftaran Mitra & Agen Koran Baru', false);
        $response->assertSee('admin-map');
    }

    /**
     * Test admin can store new agent with valid coordinates and details.
     */
    public function test_admin_can_store_new_agent(): void
    {
        $cabang = Cabang::where('kode_cabang', 'tulungagung')->first();

        $data = [
            'nama_agen' => 'Kios Koran Sejahtera',
            'nama_pemilik' => 'Bambang Sudarsono',
            'cabang_id' => $cabang->id,
            'tipe_agen' => 'Kios Eceran',
            'status' => 'aktif',
            'latitude' => -8.059625,
            'longitude' => 111.9071825,
            'nomor_whatsapp' => '+62 812-3456-7890',
            'alamat_lengkap' => 'Jl. Pahlawan No. 99, Kedungwaru, Tulungagung',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.agents.store'), $data);

        $response->assertRedirect(route('admin.agents.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('agents', [
            'nama_agen' => 'Kios Koran Sejahtera',
            'nama_pemilik' => 'Bambang Sudarsono',
            'cabang_id' => $cabang->id,
        ]);
    }

    /**
     * Test store validation fails when required fields or coordinates are missing.
     */
    public function test_store_agent_validation_fails_on_missing_required_fields(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.agents.store'), [
            'nama_agen' => '',
            'nama_pemilik' => '',
            'latitude' => 'invalid-lat',
        ]);

        $response->assertSessionHasErrors(['nama_agen', 'nama_pemilik', 'cabang_id', 'tipe_agen', 'status', 'latitude', 'longitude']);
    }

    /**
     * Test admin can view edit page.
     */
    public function test_admin_can_view_edit_agent_page(): void
    {
        $agent = Agent::first();

        $response = $this->actingAs($this->admin)->get(route('admin.agents.edit', $agent));

        $response->assertStatus(200);
        $response->assertSee($agent->nama_agen);
        $response->assertSee($agent->nama_pemilik);
        $response->assertSee('admin-map');
    }

    /**
     * Test admin can update agent data and coordinates.
     */
    public function test_admin_can_update_agent(): void
    {
        $agent = Agent::first();
        $cabangBlitar = Cabang::where('kode_cabang', 'blitar')->first();

        $updateData = [
            'nama_agen' => 'Kios Koran Terupdate',
            'nama_pemilik' => 'Pemilik Baru',
            'cabang_id' => $cabangBlitar->id,
            'tipe_agen' => 'Sub-Agen Loper',
            'status' => 'aktif',
            'latitude' => -8.0931251,
            'longitude' => 112.1789795,
            'nomor_whatsapp' => '+62 813-9999-8888',
            'alamat_lengkap' => 'Jl. Merdeka No. 10, Blitar',
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.agents.update', $agent), $updateData);

        $response->assertRedirect(route('admin.agents.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('agents', [
            'id' => $agent->id,
            'nama_agen' => 'Kios Koran Terupdate',
            'nama_pemilik' => 'Pemilik Baru',
            'cabang_id' => $cabangBlitar->id,
            'status' => 'aktif',
        ]);
    }

    /**
     * Test admin can delete agent.
     */
    public function test_admin_can_delete_agent(): void
    {
        $cabang = Cabang::first();

        $agent = Agent::create([
            'nama_agen' => 'Kios To Delete',
            'nama_pemilik' => 'Pemilik Temporary',
            'cabang_id' => $cabang->id,
            'tipe_agen' => 'Kios Eceran',
            'status' => 'nonaktif',
            'latitude' => -8.0669383,
            'longitude' => 111.70837,
            'nomor_whatsapp' => '0812345678',
            'alamat_lengkap' => 'Alamat Sementara',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.agents.destroy', $agent));

        $response->assertRedirect(route('admin.agents.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('agents', [
            'id' => $agent->id,
            'nama_agen' => 'Kios To Delete',
        ]);
    }
}
