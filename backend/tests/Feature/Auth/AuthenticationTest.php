<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'correct-horse-battery';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_users_can_register(): void
    {
        $this->post('/register', ['name' => 'Ada', 'email' => 'ada@example.com', 'password' => self::PASSWORD, 'password_confirmation' => self::PASSWORD])
            ->assertRedirect('/home');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'ada@example.com']);
    }

    public function test_registration_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'ada@example.com']);

        $this->post('/register', ['name' => 'Ada', 'email' => 'ada@example.com', 'password' => self::PASSWORD, 'password_confirmation' => self::PASSWORD])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_users_can_log_in_and_out(): void
    {
        $user = User::factory()->create(['password' => self::PASSWORD]);

        $this->post('/login', ['email' => $user->email, 'password' => self::PASSWORD])->assertRedirect('/home');
        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_wrong_password_is_rejected(): void
    {
        $user = User::factory()->create(['password' => self::PASSWORD]);

        $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password-value'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_is_rate_limited(): void
    {
        $user = User::factory()->create(['password' => self::PASSWORD]);

        foreach (range(1, 5) as $attempt) {
            $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password-value']);
        }

        $this->post('/login', ['email' => $user->email, 'password' => self::PASSWORD])->assertStatus(429);
        $this->assertGuest();
    }

    public function test_state_changing_auth_endpoints_are_rate_limited(): void
    {
        foreach (range(1, 20) as $attempt) {
            $this->post('/forgot-password', ['email' => 'nobody@example.com'])->assertRedirect();
        }

        $this->post('/forgot-password', ['email' => 'nobody@example.com'])->assertStatus(429);
    }

    public function test_legacy_bcrypt_hashes_are_upgraded_on_login_when_verification_is_relaxed(): void
    {
        config(['hashing.argon.verify' => false]);
        $this->app['hash']->forgetDrivers();
        $user = User::factory()->create();
        User::whereKey($user->id)->update(['password' => password_hash(self::PASSWORD, PASSWORD_BCRYPT)]);

        $this->post('/login', ['email' => $user->email, 'password' => self::PASSWORD])->assertRedirect('/home');

        $this->assertAuthenticatedAs($user);
        $this->assertStringStartsWith('$argon2id$', $user->refresh()->password);
    }

    public function test_password_reset_response_does_not_reveal_account_existence(): void
    {
        $user = User::factory()->create();

        $known = $this->from('/forgot-password')->post('/forgot-password', ['email' => $user->email]);
        $unknown = $this->from('/forgot-password')->post('/forgot-password', ['email' => 'nobody@example.com']);

        $known->assertRedirect('/forgot-password')->assertSessionHas('status', trans('passwords.sent'));
        $unknown->assertRedirect('/forgot-password')->assertSessionHas('status', trans('passwords.sent'))->assertSessionHasNoErrors();
    }

    public function test_password_can_be_reset_with_a_valid_token(): void
    {
        $user = User::factory()->create();
        $token = app('auth.password.broker')->createToken($user);
        $newPassword = 'a-brand-new-passphrase';

        $this->post('/reset-password', ['token' => $token, 'email' => $user->email, 'password' => $newPassword, 'password_confirmation' => $newPassword])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check($newPassword, $user->refresh()->password));
    }

    public function test_password_reset_rejects_an_invalid_token(): void
    {
        $user = User::factory()->create(['password' => self::PASSWORD]);
        $newPassword = 'a-brand-new-passphrase';

        $this->post('/reset-password', ['token' => 'invalid', 'email' => $user->email, 'password' => $newPassword, 'password_confirmation' => $newPassword])
            ->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check(self::PASSWORD, $user->refresh()->password));
    }

    public function test_changing_the_password_invalidates_existing_sessions(): void
    {
        $user = User::factory()->create(['password' => self::PASSWORD]);
        $this->post('/login', ['email' => $user->email, 'password' => self::PASSWORD]);
        $this->get('/home')->assertOk();

        User::whereKey($user->id)->update(['password' => Hash::make('a-brand-new-passphrase')]);
        $this->app['auth']->forgetGuards();

        $this->get('/home')->assertRedirect('/login');
    }
}
