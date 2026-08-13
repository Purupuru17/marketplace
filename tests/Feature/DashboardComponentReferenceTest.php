<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SeedsMarketplace;
use Tests\TestCase;

class DashboardComponentReferenceTest extends TestCase
{
    use RefreshDatabase;
    use SeedsMarketplace;

    protected function setUp(): void
    {
        parent::setUp();

        $this->flushTestCache();
        $this->seedMarketplace();
    }

    protected function admin(): User
    {
        return User::where('email', 'super@gmail.com')->firstOrFail();
    }

    public function test_dashboard_requires_auth(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_dashboard_renders_component_reference(): void
    {
        $this->actingAs($this->admin())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Component Reference')
            ->assertSee('DataTables Client-side')
            ->assertSee('DataTables Server-driven')
            ->assertSee('Form Section & Field')
            ->assertSee('page-header');
    }

    public function test_group_ajax_endpoint_returns_datatables_payload(): void
    {
        $this->actingAs($this->admin())
            ->get(route('dashboard.roles-json'))
            ->assertOk()
            ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
    }

    public function test_group_ajax_supports_search_and_sort(): void
    {
        $this->actingAs($this->admin())
            ->get(route('dashboard.roles-json', [
                'draw' => 1,
                'search' => ['value' => 'Administrator'],
                'order' => [['column' => 0, 'dir' => 'asc']],
                'columns' => [['data' => 'name'], ['data' => 'guard_name']],
                'start' => 0,
                'length' => 10,
            ]))
            ->assertOk()
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonPath('data.0.name', 'Administrator');
    }

    public function test_sistem_index_pages_render_with_client_side_datatable(): void
    {
        $this->actingAs($this->admin());

        foreach (['sistem.group.index', 'sistem.user.index', 'sistem.menu.index', 'sistem.hak-akses.index'] as $route) {
            $this->get(route($route))
                ->assertOk()
                ->assertSee('x-data')
                ->assertSee('dt-per-page')
                ->assertSee('Showing')
                ->assertSee('Search');
        }
    }
}
