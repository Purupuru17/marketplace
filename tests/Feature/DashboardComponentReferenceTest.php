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

    public function test_profile_page_renders(): void
    {
        $this->actingAs($this->admin())
            ->get(route('profile'))
            ->assertOk()
            ->assertSee('Informasi Pribadi')
            ->assertSee('Change Password')
            ->assertSee('Danger Zone');
    }

    public function test_profile_requires_auth(): void
    {
        $this->get(route('profile'))->assertRedirect(route('login'));
    }

    public function test_profile_updates_personal_information(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->put(route('profile.update'), ['name' => 'Super Admin Baru', 'email' => $admin->email])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame('Super Admin Baru', $admin->fresh()->name);
    }

    public function test_profile_update_rejects_duplicate_email(): void
    {
        $other = User::factory()->create(['email' => 'orang@example.com']);

        $this->actingAs($this->admin())
            ->put(route('profile.update'), ['name' => 'Super Admin', 'email' => $other->email])
            ->assertSessionHasErrors('email');
    }

    public function test_profile_password_change_requires_current_password(): void
    {
        $this->actingAs($this->admin())
            ->put(route('profile.password'), [
                'current_password' => 'password-salah',
                'password' => 'rahasia123',
                'password_confirmation' => 'rahasia123',
            ])
            ->assertSessionHasErrors('current_password');
    }

    public function test_profile_cannot_delete_super_admin(): void
    {
        $this->actingAs($this->admin())
            ->delete(route('profile.destroy'))
            ->assertSessionHas('error')
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => 'super@gmail.com']);
    }
}
