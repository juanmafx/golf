<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_welcome_page_renders_for_guests(): void
    {
        $this->get('/')->assertOk()->assertSee('Log in')->assertSee('Register');
    }

    public function test_health_endpoint_responds(): void
    {
        $this->get('/up')->assertOk();
    }

    public function test_dashboard_redirects_guests_to_login(): void
    {
        $this->get('/home')->assertRedirect('/login');
    }

    public function test_dashboard_renders_for_authenticated_users(): void
    {
        $this->actingAs(User::factory()->create())->get('/home')->assertOk()->assertSee('You are logged in to');
    }

    public function test_user_supplied_names_are_escaped(): void
    {
        $user = User::factory()->create(['name' => '<script>alert(1)</script>']);

        $this->actingAs($user)->get('/home')
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;', false);
    }

    public function test_auth_pages_render(): void
    {
        foreach (['/login', '/register', '/forgot-password', '/reset-password/token?email=user@example.com'] as $path) {
            $this->get($path)->assertOk();
        }
    }
}
