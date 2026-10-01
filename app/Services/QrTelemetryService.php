<?php

namespace App\Services;

use DeviceDetector\DeviceDetector;
use Illuminate\Http\Request;
use Stevebauman\Location\Facades\Location;

class QrTelemetryService
{
    /**
     * Parse client device, browser, OS, and location data from an incoming request.
     *
     * @return array<string, mixed>
     */
    public function capture(Request $request): array
    {
        $userAgent = (string) $request->userAgent();
        $ip = (string) $request->ip();

        // 1. Device, OS, Browser parsing via Matomo Device Detector
        $dd = new DeviceDetector($userAgent);
        $dd->discardBotInformation(false);
        $dd->parse();

        $deviceType = 'desktop';
        if ($dd->isBot()) {
            $deviceType = 'bot';
        } elseif ($dd->isSmartphone() || $dd->isFeaturePhone()) {
            $deviceType = 'mobile';
        } elseif ($dd->isTablet()) {
            $deviceType = 'tablet';
        } elseif ($dd->isDesktop()) {
            $deviceType = 'desktop';
        }

        $brand = $dd->getBrandName() ?: null;
        $model = $dd->getModel() ?: null;

        $os = $dd->getOs();
        $platform = $os['name'] ?? null;
        $platformVersion = $os['version'] ?? null;

        $client = $dd->getClient();
        $browser = $client['name'] ?? null;
        $browserVersion = $client['version'] ?? null;

        // 2. Geolocation parsing (Cloudflare headers priority -> Location package)
        $countryCode = $request->header('CF-IPCountry');
        $city = $request->header('CF-IPCity');
        $countryName = null;
        $region = null;
        $latitude = null;
        $longitude = null;

        // If Cloudflare didn't supply full data or running locally/direct, use Location resolver
        try {
            $position = Location::get($ip);
            if ($position) {
                $countryCode = $countryCode ?: $position->countryCode;
                $countryName = $position->countryName;
                $region = $position->regionName;
                $city = $city ?: $position->cityName;
                $latitude = $position->latitude ? (float) $position->latitude : null;
                $longitude = $position->longitude ? (float) $position->longitude : null;
            }
        } catch (\Throwable) {
            // Fail open gracefully: scan redirect must never fail because of geoip lookup
        }

        // 3. IP Hashing & Privacy Masking
        $ipHash = hash('sha256', $ip.config('app.key'));
        $anonymizedIp = $this->anonymizeIp($ip);

        return [
            'ip_address' => $anonymizedIp,
            'ip_hash' => $ipHash,
            'country_code' => $countryCode ? strtoupper(substr($countryCode, 0, 2)) : null,
            'country_name' => $countryName,
            'region' => $region,
            'city' => $city,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'device_type' => $deviceType,
            'device_brand' => $brand,
            'device_model' => $model,
            'platform' => $platform,
            'platform_version' => $platformVersion,
            'browser' => $browser,
            'browser_version' => $browserVersion,
            'referrer' => $request->header('referer'),
            'user_agent' => $userAgent,
            'scanned_at' => now(),
        ];
    }

    /**
     * Anonymize an IP address (zeroing the last octet in IPv4, or network segment in IPv6).
     */
    protected function anonymizeIp(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);
            $parts[3] = '0';

            return implode('.', $parts);
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $parts = explode(':', $ip);
            $parts = array_slice($parts, 0, 3);

            return implode(':', $parts).'::';
        }

        return $ip;
    }
}
