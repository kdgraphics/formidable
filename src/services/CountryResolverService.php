<?php

namespace Inspire\Formidable\services;

use Craft;
use craft\base\Component;
use craft\web\Request;
use Inspire\Formidable\Formidable;

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

    public function getLookup(): CountryLookupService
    {
        /** @var CountryLookupService */
        return Formidable::getInstance()->get('countryLookup');
    }

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
            $ipAddress = $this->getLookup()->extractPublicIpAddress((string)$request->headers->get($header, ''));

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

        return $this->getLookup()->extractCountryCode($site->language);
    }

    private function lookupCountryCodeForIp(string $ipAddress): ?string
    {
        return $this->getLookup()->lookupCountryCode([
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

        $countryCode = $this->getLookup()->lookupCountryCode([
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
    }

    private function normalizeCountryCode(?string $value): ?string
    {
        return $this->getLookup()->normalizeCountryCode($value);
    }
}
