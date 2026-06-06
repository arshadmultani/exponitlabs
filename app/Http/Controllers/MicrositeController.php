<?php

namespace App\Http\Controllers;

use App\Models\Microsite;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MicrositeController extends Controller
{
    /**
     * Public doctor website. The QR / shared link points here.
     */
    public function show(string $slug)
    {
        try {
            $microsite = Microsite::where('url', $slug)
                ->with([
                    'doctor',
                    'doctor.reviews' => fn ($query) => $query->whereNotNull('verified_at')->latest(),
                    'doctor.showcases',
                ])
                ->firstOrFail();
        } catch (ModelNotFoundException) {
            return response()->view('errors.404-doctor', [], 404);
        }

        $groupedShowcases = $microsite->doctor?->showcases?->groupBy('media_type');

        return view('microsite.show', [
            'microsite' => $microsite,
            'groupedShowcases' => $groupedShowcases,
        ]);
    }
}
