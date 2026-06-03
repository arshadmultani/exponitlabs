<?php

namespace App\Http\Controllers;

use App\Models\ArCreative;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ArController extends Controller
{
    /**
     * The public AR experience a doctor lands on after scanning the QR.
     */
    public function show(ArCreative $creative)
    {
        // A half-finished creative can't render for anyone.
        if (! $creative->isReady()) {
            throw new NotFoundHttpException;
        }

        // Drafts are previewable by a signed-in admin only; the public (the
        // doctor scanning the QR, with no session) only ever sees published ones.
        if (! $creative->isPublished() && ! auth()->check()) {
            throw new NotFoundHttpException;
        }

        // Never cache the HTML, so a phone always loads the page that points at
        // the latest built JS/CSS (the hashed assets themselves stay cacheable).
        return response()
            ->view('ar.show', ['creative' => $creative])
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
    }

    /**
     * Store the MindAR ".mind" tracking file compiled in the admin's browser.
     */
    public function storeMind(Request $request, ArCreative $creative)
    {
        $request->validate([
            'mind' => ['required', 'file', 'max:20480'], // up to ~20 MB
            'tracking_score' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        // Overwrite any previous tracking file for this creative.
        if ($creative->mind_file_path) {
            Storage::disk('public')->delete($creative->mind_file_path);
        }

        $path = $request->file('mind')->storeAs(
            'ar/mind',
            "{$creative->slug}.mind",
            'public',
        );

        $creative->update([
            'mind_file_path' => $path,
            'tracking_score' => (int) $request->integer('tracking_score'),
        ]);

        return response()->json([
            'ok' => true,
            'tracking_score' => $creative->tracking_score,
        ]);
    }
}
