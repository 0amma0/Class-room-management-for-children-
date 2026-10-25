<?php

it('adds security headers to public responses', function () {
    $response = $this->get('/up');

    $response->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()')
        ->assertHeader('X-Permitted-Cross-Domain-Policies', 'none');
});

it('adds strict transport security only for production https responses', function () {
    app()->detectEnvironment(fn () => 'production');

    $this->get('https://example.test/up')
        ->assertOk()
        ->assertHeader('Strict-Transport-Security', 'max-age=31536000');

    $this->get('http://example.test/up')
        ->assertOk()
        ->assertHeaderMissing('Strict-Transport-Security');
});
