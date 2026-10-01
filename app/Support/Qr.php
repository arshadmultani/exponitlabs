<?php

namespace App\Support;

use App\Models\ArCreative;
use App\Models\QrCode as QrCodeModel;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Facades\Storage;

/**
 * Pure-PHP QR generation (endroid/qr-code). No external service is contacted.
 */
class Qr
{
    /**
     * Build a print-ready QR that points at the creative's public AR page.
     */
    public static function forCreative(ArCreative $creative, string $format = 'png'): ResultInterface
    {
        $writer = $format === 'svg' ? new SvgWriter : new PngWriter;

        return (new Builder(
            writer: $writer,
            data: $creative->arUrl(),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 600,
            margin: 16,
            labelText: $creative->name,
        ))->build();
    }

    /**
     * Build a dynamic QR code with custom colors, sizing, and optional center logo.
     */
    public static function forQrCode(QrCodeModel $qrCode, string $format = 'png'): ResultInterface
    {
        $writer = $format === 'svg' ? new SvgWriter : new PngWriter;
        $design = array_merge(QrCodeModel::defaultDesign(), $qrCode->design ?? []);

        $fgColor = static::hexToColor($design['foreground_color'] ?? '#0f172a');
        $bgColor = static::hexToColor($design['background_color'] ?? '#ffffff');

        $level = match (strtolower((string) ($design['error_correction'] ?? 'high'))) {
            'low' => ErrorCorrectionLevel::Low,
            'medium' => ErrorCorrectionLevel::Medium,
            'quartile' => ErrorCorrectionLevel::Quartile,
            default => ErrorCorrectionLevel::High,
        };

        $size = (int) ($design['size'] ?? 600);
        $margin = (int) ($design['margin'] ?? 16);

        $logoPath = null;
        if (! empty($design['logo_path']) && Storage::disk('public')->exists($design['logo_path'])) {
            $logoPath = Storage::disk('public')->path($design['logo_path']);
        }

        $labelText = ! empty($design['label_text']) ? (string) $design['label_text'] : '';

        return (new Builder(
            writer: $writer,
            data: $qrCode->shortUrl(),
            errorCorrectionLevel: $level,
            size: $size,
            margin: $margin,
            foregroundColor: $fgColor,
            backgroundColor: $bgColor,
            labelText: $labelText,
            logoPath: $logoPath ?: '',
            logoResizeToWidth: $logoPath ? (int) ($size * 0.22) : null,
            logoPunchoutBackground: true,
        ))->build();
    }

    /**
     * Convert hexadecimal color string to Color instance.
     */
    public static function hexToColor(string $hex): Color
    {
        $clean = ltrim($hex, '#');

        if (strlen($clean) === 3) {
            $r = (int) hexdec(str_repeat(substr($clean, 0, 1), 2));
            $g = (int) hexdec(str_repeat(substr($clean, 1, 1), 2));
            $b = (int) hexdec(str_repeat(substr($clean, 2, 1), 2));
        } elseif (strlen($clean) >= 6) {
            $r = (int) hexdec(substr($clean, 0, 2));
            $g = (int) hexdec(substr($clean, 2, 2));
            $b = (int) hexdec(substr($clean, 4, 2));
        } else {
            $r = 15;
            $g = 23;
            $b = 42;
        }

        return new Color($r, $g, $b);
    }
}
