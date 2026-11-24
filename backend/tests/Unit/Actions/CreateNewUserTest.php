<?php

namespace Tests\Unit\Actions;

use App\Actions\Fortify\CreateNewUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CreateNewUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_a_salted_argon2id_hash_and_normalizes_email(): void
    {
        $password = 'correct-horse-battery';
        $first = (new CreateNewUser)->create(['name' => 'Ada', 'email' => 'Ada@Example.com', 'password' => $password, 'password_confirmation' => $password]);
        $second = (new CreateNewUser)->create(['name' => 'Bob', 'email' => 'bob@example.com', 'password' => $password, 'password_confirmation' => $password]);

        $this->assertSame('ada@example.com', $first->email);
        $this->assertStringStartsWith('$argon2id$', $first->password);
        $this->assertNotSame($first->password, $second->password);
        $this->assertTrue(Hash::check($password, $first->password));
    }

    #[DataProvider('invalidInputProvider')]
    public function test_it_rejects_invalid_input(array $overrides, string $field): void
    {
        $input = array_merge(['name' => 'Ada', 'email' => 'ada@example.com', 'password' => 'correct-horse-battery', 'password_confirmation' => 'correct-horse-battery'], $overrides);

        try {
            (new CreateNewUser)->create($input);
            $this->fail('Validation should have failed.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey($field, $exception->errors());
        }
    }

    public static function invalidInputProvider(): array
    {
        return [
            'missing name' => [['name' => ''], 'name'],
            'invalid email' => [['email' => 'not-an-email'], 'email'],
            'short password' => [['password' => 'short', 'password_confirmation' => 'short'], 'password'],
            'oversized password' => [['password' => str_repeat('a', 129), 'password_confirmation' => str_repeat('a', 129)], 'password'],
            'mismatched confirmation' => [['password_confirmation' => 'different-password'], 'password'],
        ];
    }
}
