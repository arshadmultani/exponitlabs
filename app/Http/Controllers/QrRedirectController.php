<?php

namespace App\Http\Controllers;

use App\Jobs\RecordQrScanJob;
use App\Models\QrCode;
use App\Services\QrTelemetryService;
use App\Support\Qr;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class QrRedirectController extends Controller
{
    public function __construct(
        public QrTelemetryService $telemetryService,
    ) {}

    /**
     * Handle incoming dynamic QR code scan redirect and telemetry recording.
     */
    public function redirect(Request $request, string $code): RedirectResponse|Response
    {
        $qrCode = QrCode::where('code', $code)->first();

        if (! $qrCode) {
            abort(404, 'QR code not found.');
        }

        // Check if QR code is inactive or expired
        if (! $qrCode->is_active || $qrCode->isExpired()) {
            return redirect()->away($qrCode->getEffectiveFallbackUrl(), 302);
        }

        // Capture client telemetry (Device, OS, Browser, Geolocation)
        $telemetry = $this->telemetryService->capture($request);

        // Check unique visitor cookie
        $cookieName = "qr_visited_{$qrCode->id}";
        $isUnique = ! $request->hasCookie($cookieName);

        // Dispatch background job to log scan and update counters
        RecordQrScanJob::dispatch($qrCode->id, $telemetry, $isUnique);

        // Resolve platform-specific destination URL (smart routing for iOS/Android if set)
        $destinationUrl = $qrCode->getEffectiveDestinationUrl($telemetry['platform']);

        // Issue HTTP 307 temporary redirect (prevents browser from caching destination URL)
        $response = redirect()->away($destinationUrl, 307);

        if ($isUnique) {
            $response->withCookie(cookie($cookieName, '1', 60 * 24 * 30));
        }

        return $response;
    }

    /**
     * Download the QR code in PNG or SVG format.
     */
    public function download(string $code, string $format = 'png'): Response
    {
        $qrCode = QrCode::where('code', $code)->firstOrFail();
        $format = in_array(strtolower($format), ['svg', 'png']) ? strtolower($format) : 'png';
        $result = Qr::forQrCode($qrCode, $format);

        $filename = "qr-{$qrCode->code}.{$format}";

        return response($result->getString(), 200, [
            'Content-Type' => $result->getMimeType(),
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Preview the QR code image inline.
     */
    public function preview(string $code, string $format = 'svg'): Response
    {
        $qrCode = QrCode::where('code', $code)->firstOrFail();
        $format = in_array(strtolower($format), ['svg', 'png']) ? strtolower($format) : 'svg';
        $result = Qr::forQrCode($qrCode, $format);

        return response($result->getString(), 200, [
            'Content-Type' => $result->getMimeType(),
            'Cache-Control' => 'no-cache, private',
        ]);
    }
}
