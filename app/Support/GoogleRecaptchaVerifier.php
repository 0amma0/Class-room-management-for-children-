<?php

namespace App\Support;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleRecaptchaVerifier
{
    public function verify(string $token, ?string $ipAddress = null): bool
    {
        $payload = [
            'secret' => config('services.recaptcha.secret_key'),
            'response' => $token,
        ];

        if ($ipAddress !== null) {
            $payload['remoteip'] = $ipAddress;
        }

        try {
            $response = Http::asForm()
                ->acceptJson()
                ->connectTimeout(3)
                ->timeout(5)
                ->post('https://www.google.com/recaptcha/api/siteverify', $payload);
        } catch (ConnectionException $exception) {
            Log::warning('Google reCAPTCHA verification could not connect.', [
                'exception' => $exception::class,
            ]);

            return false;
        }

        if (! $response->successful()) {
            Log::warning('Google reCAPTCHA verification returned an unsuccessful HTTP response.', [
                'status' => $response->status(),
            ]);

            return false;
        }

        $hostname = config('services.recaptcha.hostname');
        $verifiedHostname = $response->json('hostname');

        return $response->json('success') === true
            && is_string($hostname)
            && is_string($verifiedHostname)
            && hash_equals(mb_strtolower($hostname), mb_strtolower($verifiedHostname));
    }
}
