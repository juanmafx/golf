<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'correct-horse-battery';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function enrolledUser(): User
    {
        $user = User::factory()->create(['password' => self::PASSWORD]);
        $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time()]);
        $this->post('/user/two-factor-authentication')->assertSessionHasNoErrors();
        $user->refresh();
        $code = app(Google2FA::class)->getCurrentOtp(decrypt($user->two_factor_secret));
        $this->post('/user/confirmed-two-factor-authentication', ['code' => $code])->assertSessionHasNoErrors();
        $this->post('/logout');
        $this->app['auth']->forgetGuards();
        Cache::flush();

        return $user->refresh();
    }

    public function test_security_page_requires_password_confirmation(): void
    {
        $this->actingAs(User::factory()->create())->get('/account/security')->assertRedirect('/user/confirm-password');
    }

    public function test_enrollment_requires_confirmed_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/user/two-factor-authentication')->assertRedirect('/user/confirm-password');
        $this->assertNull($user->refresh()->two_factor_secret);
    }

    public function test_pending_enrollment_shows_a_qr_code_compatible_with_the_policy(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time()]);
        $this->post('/user/two-factor-authentication');

        $this->get('/account/security')->assertOk()->assertSee('<svg', false)->assertDontSee('style=', false);
    }

    public function test_enrollment_is_not_active_until_confirmed(): void
    {
        $user = User::factory()->create(['password' => self::PASSWORD]);
        $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time()]);
        $this->post('/user/two-factor-authentication');
        $this->post('/user/confirmed-two-factor-authentication', ['code' => '000000']);

        $this->assertNull($user->refresh()->two_factor_confirmed_at);
    }

    public function test_secrets_are_encrypted_at_rest_and_hidden_from_serialization(): void
    {
        $user = $this->enrolledUser();
        $row = DB::table('users')->where('id', $user->id)->first();
        $plainSecret = decrypt($row->two_factor_secret);

        $this->assertNotSame($plainSecret, $row->two_factor_secret);
        $this->assertStringNotContainsString($plainSecret, $row->two_factor_secret);
        $this->assertArrayNotHasKey('two_factor_secret', $user->toArray());
        $this->assertArrayNotHasKey('two_factor_recovery_codes', $user->toArray());
        $this->assertArrayNotHasKey('password', $user->toArray());
    }

    public function test_login_requires_a_valid_totp_code(): void
    {
        $user = $this->enrolledUser();

        $this->post('/login', ['email' => $user->email, 'password' => self::PASSWORD])->assertRedirect('/two-factor-challenge');
        $this->assertGuest();

        $this->post('/two-factor-challenge', ['code' => '000000'])->assertSessionHasErrors('code');
        $this->assertGuest();

        $code = app(Google2FA::class)->getCurrentOtp(decrypt($user->two_factor_secret));
        $this->post('/two-factor-challenge', ['code' => $code])->assertRedirect('/home');
        $this->assertAuthenticatedAs($user);
    }

    public function test_a_totp_code_cannot_be_replayed(): void
    {
        $user = $this->enrolledUser();
        $code = app(Google2FA::class)->getCurrentOtp(decrypt($user->two_factor_secret));

        $this->post('/login', ['email' => $user->email, 'password' => self::PASSWORD]);
        $this->post('/two-factor-challenge', ['code' => $code])->assertRedirect('/home');

        $this->post('/logout');
        $this->app['auth']->forgetGuards();
        $this->post('/login', ['email' => $user->email, 'password' => self::PASSWORD]);
        $this->post('/two-factor-challenge', ['code' => $code])->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_recovery_codes_work_only_once(): void
    {
        $user = $this->enrolledUser();
        $recoveryCode = $user->recoveryCodes()[0];

        $this->post('/login', ['email' => $user->email, 'password' => self::PASSWORD]);
        $this->post('/two-factor-challenge', ['recovery_code' => $recoveryCode])->assertRedirect('/home');
        $this->assertAuthenticatedAs($user);
        $this->assertNotContains($recoveryCode, $user->refresh()->recoveryCodes());

        $this->post('/logout');
        $this->app['auth']->forgetGuards();
        $this->post('/login', ['email' => $user->email, 'password' => self::PASSWORD]);
        $this->post('/two-factor-challenge', ['recovery_code' => $recoveryCode])->assertSessionHasErrors('recovery_code');
        $this->assertGuest();
    }

    public function test_two_factor_challenge_is_rate_limited(): void
    {
        $user = $this->enrolledUser();
        $this->post('/login', ['email' => $user->email, 'password' => self::PASSWORD]);

        foreach (range(1, 5) as $attempt) {
            $this->post('/two-factor-challenge', ['code' => '000000']);
        }

        $this->post('/two-factor-challenge', ['code' => '000000'])->assertStatus(429);
    }
}
