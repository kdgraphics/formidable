<?php

namespace Inspire\Formidable\services;

use Craft;
use craft\base\Component;
use Throwable;

class CountryLookupService extends Component
{
    /**
     * @param array<int, array{url: string, type: string, field?: string}> $providers
     */
    public function lookupCountryCode(array $providers): ?string
    {
        foreach ($providers as $provider) {
            $countryCode = $this->lookupCountryCodeFromProvider($provider);

            if ($countryCode !== null) {
                return $countryCode;
            }
        }

        return null;
    }

    public function extractCountryCodeFromJson(string $body, string $field): ?string
    {
        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return null;
        }

        if (!is_array($data)) {
            return null;
        }

        return $this->normalizeCountryCode($data[$field] ?? null);
    }

    public function extractPublicIpAddress(string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        return $this->extractForwardedIpAddress($value)
            ?? $this->extractDelimitedIpAddress($value)
            ?? $this->normalizeIpAddress($value);
    }

    public function extractCountryCode(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $parts = preg_split('/[-_]/', $value);

        if ($parts === false || count($parts) < 2) {
            return null;
        }

        foreach (array_slice($parts, 1) as $part) {
            if (preg_match('/^[A-Za-z]{2}$/', $part) === 1) {
                return $this->normalizeCountryCode($part);
            }
        }

        return null;
    }

    public function normalizeCountryCode(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $countryCode = strtolower(trim($value));

        if (!preg_match('/^[a-z]{2}$/', $countryCode)) {
            return null;
        }

        if (in_array($countryCode, ['xx', 't1'], true)) {
            return null;
        }

        return $countryCode;
    }

    /**
     * @param array{url: string, type: string, field?: string} $provider
     */
    private function lookupCountryCodeFromProvider(array $provider): ?string
    {
        try {
            $response = Craft::createGuzzleClient([
                'timeout' => 2,
                'connect_timeout' => 2,
                'http_errors' => false,
            ])->get($provider['url']);

            if ($response->getStatusCode() !== 200) {
                return null;
            }

            return match ($provider['type']) {
                'json' => $this->extractCountryCodeFromJson(
                    (string)$response->getBody(),
                    $provider['field'] ?? 'country_code'
                ),
                default => $this->normalizeCountryCode(trim((string)$response->getBody())),
            };
        } catch (Throwable) {
            return null;
        }
    }

    private function extractForwardedIpAddress(string $value): ?string
    {
        if (!str_contains(strtolower($value), 'for=')) {
            return null;
        }

        preg_match_all('/for=(?:"?\\[?)([^;,"\]\s]+)/i', $value, $matches);

        foreach ($matches[1] ?? [] as $candidate) {
            $ipAddress = $this->normalizeIpAddress($candidate);

            if ($ipAddress !== null) {
                return $ipAddress;
            }
        }

        return null;
    }

    private function extractDelimitedIpAddress(string $value): ?string
    {
        foreach (preg_split('/\s*,\s*/', $value) ?: [] as $candidate) {
            $ipAddress = $this->normalizeIpAddress($candidate);

            if ($ipAddress !== null) {
                return $ipAddress;
            }
        }

        return null;
    }

    private function normalizeIpAddress(string $value): ?string
    {
        $candidate = trim($value, " \t\n\r\0\x0B\"'[]");

        if ($candidate === '' || (str_contains($candidate, ':') && str_contains($candidate, '.'))) {
            $parts = explode(':', $candidate);
            $candidate = $parts[0];
        }

        if (filter_var($candidate, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return null;
        }

        return $candidate;
    }
}
