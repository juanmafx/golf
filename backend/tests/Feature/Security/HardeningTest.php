<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class HardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function confirmedUser(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time()]);
        $this->post('/user/two-factor-authentication');
        $user->refresh();
        $code = app(Google2FA::class)->getCurrentOtp(decrypt($user->two_factor_secret));
        $this->post('/user/confirmed-two-factor-authentication', ['code' => $code]);

        return $user->refresh();
    }

    public function test_password_reset_links_ignore_a_forged_host_header(): void
    {
        config(['app.url' => 'https://golf.example']);
        Notification::fake();
        $user = User::factory()->create();

        $this->post('http://attacker.example/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
            return parse_url($notification->toMail($user)->actionUrl, PHP_URL_HOST) === 'golf.example';
        });
    }

    public function test_login_with_a_non_string_email_is_a_validation_error(): void
    {
        $this->post('/login', ['email' => ['someone@example.com'], 'password' => 'irrelevant'])
            ->assertStatus(302)
            ->assertSessionHasErrors('email');
    }

    public function test_force_cannot_replace_a_confirmed_two_factor_secret(): void
    {
        $user = $this->confirmedUser();
        $secret = decrypt($user->two_factor_secret);
        $recoveryCodes = $user->recoveryCodes();

        $this->post('/user/two-factor-authentication', ['force' => true]);

        $user->refresh();
        $this->assertSame($secret, decrypt($user->two_factor_secret));
        $this->assertSame($recoveryCodes, $user->recoveryCodes());
    }

    public function test_force_can_regenerate_a_pending_two_factor_secret(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time()]);
        $this->post('/user/two-factor-authentication');
        $secret = decrypt($user->refresh()->two_factor_secret);

        $this->post('/user/two-factor-authentication', ['force' => true]);

        $this->assertNotSame($secret, decrypt($user->refresh()->two_factor_secret));
    }

    public function test_pages_showing_two_factor_material_are_not_stored(): void
    {
        $this->confirmedUser();

        $page = $this->get('/account/security')->assertOk();
        $json = $this->getJson('/user/two-factor-recovery-codes')->assertOk();

        $this->assertStringContainsString('no-store', (string) $page->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', (string) $json->headers->get('Cache-Control'));
    }

    public function test_not_found_responses_carry_security_headers(): void
    {
        $this->get('/does-not-exist')
            ->assertNotFound()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY');
    }

    public function test_page_views_do_not_consume_the_write_rate_limit(): void
    {
        foreach (range(1, 25) as $view) {
            $this->get('/login')->assertOk();
        }

        $this->post('/forgot-password', ['email' => 'nobody@example.com'])->assertStatus(302);
    }

    public function test_write_requests_are_still_rate_limited(): void
    {
        foreach (range(1, 20) as $attempt) {
            $this->post('/forgot-password', ['email' => 'nobody@example.com']);
        }

        $this->post('/forgot-password', ['email' => 'nobody@example.com'])->assertStatus(429);
    }

    public function test_error_pages_comply_with_the_content_security_policy(): void
    {
        $content = (string) $this->get('/does-not-exist')->assertNotFound()->getContent();

        $this->assertStringNotContainsString('<style', $content);
        $this->assertStringNotContainsString('style=', $content);
    }

    public function test_local_disk_upload_and_download_routes_are_not_exposed(): void
    {
        $this->assertFalse(Route::has('storage.local'));
        $this->assertFalse(Route::has('storage.local.upload'));
    }
}
