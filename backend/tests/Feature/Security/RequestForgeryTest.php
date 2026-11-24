<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequestForgeryTest extends TestCase
{
    use RefreshDatabase;

    public function test_state_changing_requests_without_a_token_are_rejected(): void
    {
        $this->app->instance(PreventRequestForgery::class, new class($this->app, $this->app['encrypter']) extends PreventRequestForgery
        {
            protected function runningUnitTests()
            {
                return false;
            }
        });
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout')->assertStatus(419);
        $this->assertAuthenticatedAs($user);
    }

    public function test_responses_carry_security_headers(): void
    {
        $this->withoutVite();

        $this->get('/login')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'no-referrer');
    }
}
