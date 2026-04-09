<?php

namespace Inspire\Formidable\services;

use Craft;
use craft\base\Component;
use craft\web\Request;
use Throwable;

class CountryResolverService extends Component
{
    private const DEFAULT_COUNTRY_CODE = 'gb';
    private const GEO_LOOKUP_CACHE_DURATION = 86400;

    private const COUNTRY_HEADERS = [
        'CF-IPCountry',
        'CloudFront-Viewer-Country',
        'X-Vercel-IP-Country',
        'Fastly-Geo-Country-Code',
        'X-Country-Code',
        'X-Country',
        'Geo-Country-Code',
    ];

    private const CLIENT_IP_HEADERS = [
        'CF-Connecting-IP',
        'True-Client-IP',
        'X-Real-IP',
        'Fly-Client-IP',
        'X-Client-IP',
        'Client-IP',
        'X-Forwarded-For',
        'Forwarded',
    ];

    /**
     * Resolve the best available ISO 3166-1 alpha-2 country code for phone inputs.
     */
    public function getPhoneFieldCountryCode(): string
    {
        $request = Craft::$app->getRequest();

        return $this->normalizeCountryCode(
            $this->getCountryFromHeaders($request)
            ?? $this->getCountryFromIpAddress($request)
            ?? $this->getCountryFromCurrentEnvironment()
            ?? $this->getCountryFromCurrentSite()
            ?? self::DEFAULT_COUNTRY_CODE
        );
    }

    private function getCountryFromHeaders(Request $request): ?string
    {
        foreach (self::COUNTRY_HEADERS as $header) {
            $countryCode = $this->normalizeCountryCode($request->headers->get($header));

            if ($countryCode !== null) {
                return $countryCode;
            }
        }

        return null;
    }

    private function getCountryFromIpAddress(Request $request): ?string
    {
        $ipAddress = $this->getClientIpAddress($request);

        if ($ipAddress === null) {
            return null;
        }

        $cacheKey = 'formidable-country:' . sha1($ipAddress);
        $cache = Craft::$app->getCache();
        $cachedCountryCode = $cache->get($cacheKey);

        if (is_string($cachedCountryCode) && $cachedCountryCode !== '') {
            return $this->normalizeCountryCode($cachedCountryCode);
        }

        $countryCode = $this->lookupCountryCodeForIp($ipAddress);

        if ($countryCode !== null) {
            $cache->set($cacheKey, $countryCode, self::GEO_LOOKUP_CACHE_DURATION);
        }

        return $countryCode;
    }

    private function getClientIpAddress(Request $request): ?string
    {
        foreach (self::CLIENT_IP_HEADERS as $header) {
            $ipAddress = $this->extractPublicIpAddress((string)$request->headers->get($header, ''));

            if ($ipAddress !== null) {
                return $ipAddress;
            }
        }

        $ipAddress = $request->getUserIP(FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);

        if (is_string($ipAddress) && $ipAddress !== '') {
            return $ipAddress;
        }

        return null;
    }

    private function getCountryFromCurrentSite(): ?string
    {
        $site = Craft::$app->getSites()->getCurrentSite();

        if ($site === null) {
            return null;
        }

        return $this->extractCountryCode($site->language);
    }

    private function lookupCountryCodeForIp(string $ipAddress): ?string
    {
        return $this->lookupCountryCode([
            [
                'url' => "https://ipapi.co/{$ipAddress}/country/",
                'type' => 'text',
            ],
            [
                'url' => "https://ipwho.is/{$ipAddress}",
                'type' => 'json',
                'field' => 'country_code',
            ],
        ]);
    }

    private function getCountryFromCurrentEnvironment(): ?string
    {
        if (Craft::$app->getConfig()->getGeneral()->devMode !== true) {
            return null;
        }

        $cacheKey = 'formidable-country:environment';
        $cache = Craft::$app->getCache();
        $cachedCountryCode = $cache->get($cacheKey);

        if (is_string($cachedCountryCode) && $cachedCountryCode !== '') {
            return $this->normalizeCountryCode($cachedCountryCode);
        }

        try {
            $countryCode = $this->lookupCountryCode([
                [
                    'url' => 'https://ipapi.co/country/',
                    'type' => 'text',
                ],
                [
                    'url' => 'https://ipwho.is',
                    'type' => 'json',
                    'field' => 'country_code',
                ],
            ]);

            if ($countryCode !== null) {
                $cache->set($cacheKey, $countryCode, self::GEO_LOOKUP_CACHE_DURATION);
            }

            return $countryCode;
        } catch (Throwable) {
            return null;
        }
    }

    private function lookupCountryCode(array $providers): ?string
    {
        foreach ($providers as $provider) {
            try {
                $response = Craft::createGuzzleClient([
                    'timeout' => 2,
                    'connect_timeout' => 2,
                    'http_errors' => false,
                ])->get($provider['url']);

                if ($response->getStatusCode() !== 200) {
                    continue;
                }

                $countryCode = match ($provider['type']) {
                    'json' => $this->extractCountryCodeFromJson(
                        (string)$response->getBody(),
                        $provider['field'] ?? 'country_code'
                    ),
                    default => $this->normalizeCountryCode(trim((string)$response->getBody())),
                };

                if ($countryCode !== null) {
                    return $countryCode;
                }
            } catch (Throwable) {
                continue;
            }
        }

        return null;
    }

    private function extractCountryCodeFromJson(string $body, string $field): ?string
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

    private function extractPublicIpAddress(string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        if (str_contains(strtolower($value), 'for=')) {
            preg_match_all('/for=(?:"?\\[?)([^;,"\]\s]+)/i', $value, $matches);

            foreach ($matches[1] ?? [] as $candidate) {
                $ipAddress = $this->normalizeIpAddress($candidate);

                if ($ipAddress !== null) {
                    return $ipAddress;
                }
            }
        }

        foreach (preg_split('/\s*,\s*/', $value) ?: [] as $candidate) {
            $ipAddress = $this->normalizeIpAddress($candidate);

            if ($ipAddress !== null) {
                return $ipAddress;
            }
        }

        return $this->normalizeIpAddress($value);
    }

    private function normalizeIpAddress(string $value): ?string
    {
        $candidate = trim($value, " \t\n\r\0\x0B\"'[]");

        if ($candidate === '' || str_contains($candidate, ':') && str_contains($candidate, '.')) {
            $parts = explode(':', $candidate);
            $candidate = $parts[0];
        }

        if (filter_var($candidate, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return null;
        }

        return $candidate;
    }

    private function extractCountryCode(?string $value): ?string
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

    private function normalizeCountryCode(?string $value): ?string
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
}
