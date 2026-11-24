<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_it_sets_a_restrictive_policy(): void
    {
        $response = (new SecurityHeaders)->handle(Request::create('http://localhost/'), fn () => new Response('ok'));
        $policy = $response->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("default-src 'none'", $policy);
        $this->assertStringContainsString("frame-ancestors 'none'", $policy);
        $this->assertStringNotContainsString('unsafe-inline', $policy);
        $this->assertStringNotContainsString('unsafe-eval', $policy);
        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertSame('DENY', $response->headers->get('X-Frame-Options'));
    }

    public function test_it_sends_hsts_only_over_https(): void
    {
        $middleware = new SecurityHeaders;
        $plain = $middleware->handle(Request::create('http://localhost/'), fn () => new Response('ok'));
        $secure = $middleware->handle(Request::create('https://localhost/'), fn () => new Response('ok'));

        $this->assertFalse($plain->headers->has('Strict-Transport-Security'));
        $this->assertTrue($secure->headers->has('Strict-Transport-Security'));
    }
}
