<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\DCR;
use App\Models\DCRProduct;
use App\Models\DCRPromotionalInput;
use App\Models\Doctor;
use App\Models\Product;
use App\Models\PromotionalInput;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SyncController extends Controller
{
    /**
     * Download assigned master data (Doctors, Products, Promotional Inputs, Visit History).
     */
    public function masterData(Request $request): JsonResponse
    {
        $since = $request->query('since');

        $doctorsQuery = Doctor::query()->where('status', 'active');
        $productsQuery = Product::query();
        $inputsQuery = PromotionalInput::query();

        if ($since) {
            $doctorsQuery->where('updated_at', '>=', $since);
            $productsQuery->where('updated_at', '>=', $since);
            $inputsQuery->where('updated_at', '>=', $since);
        }

        $doctors = $doctorsQuery->get([
            'id',
            'uuid',
            'name',
            'email',
            'phone',
            'specialty',
            'qualification',
            'town',
            'area_id',
            'address',
            'clinic_name',
            'latitude',
            'longitude',
            'location',
            'status',
            'updated_at',
        ])->map(function ($doc) {
            if (! $doc->uuid) {
                $doc->uuid = (string) Str::uuid();
                $doc->saveQuietly();
            }

            return $doc;
        });

        $areas = Area::get(['id', 'name', 'slug']);

        $products = $productsQuery->get([
            'id',
            'name',
            'therapeutic_area_id',
            'updated_at',
        ]);

        $promotionalInputs = $inputsQuery->get([
            'id',
            'name',
            'type',
            'updated_at',
        ]);

        $recentDcrs = DCR::with(['doctor', 'sampleProducts', 'promotionalInputs'])
            ->latest('date')
            ->take(200)
            ->get()
            ->map(function ($dcr) {
                return [
                    'id' => $dcr->id,
                    'uuid' => $dcr->uuid,
                    'date' => is_string($dcr->date) ? $dcr->date : ($dcr->date ? $dcr->date->format('Y-m-d') : null),
                    'doctor_id' => $dcr->doctor_id,
                    'doctor_uuid' => $dcr->doctor?->uuid,
                    'doctor_name' => $dcr->doctor?->name,
                    'remarks' => $dcr->remarks,
                    'products' => $dcr->sampleProducts->map(fn ($p) => [
                        'product_id' => $p->product_id,
                        'quantity' => $p->quantity,
                    ]),
                    'inputs' => $dcr->promotionalInputs->map(fn ($i) => [
                        'promotional_input_id' => $i->promotional_input_id,
                        'quantity' => $i->quantity,
                    ]),
                ];
            });

        return response()->json([
            'server_time' => now()->toIso8601String(),
            'doctors' => $doctors,
            'areas' => $areas,
            'products' => $products,
            'promotional_inputs' => $promotionalInputs,
            'visit_history' => $recentDcrs,
        ]);
    }

    /**
     * Upload offline-created or updated Doctors.
     */
    public function syncDoctors(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'doctors' => 'required|array',
            'doctors.*.uuid' => 'required|string',
            'doctors.*.name' => 'required|string|max:255',
            'doctors.*.email' => 'nullable|email|max:255',
            'doctors.*.phone' => 'nullable|string|max:50',
            'doctors.*.specialty' => 'nullable|string|max:255',
            'doctors.*.qualification' => 'nullable|string|max:255',
            'doctors.*.town' => 'nullable|string|max:255',
            'doctors.*.area_id' => 'nullable|integer',
            'doctors.*.address' => 'nullable|string|max:500',
            'doctors.*.clinic_name' => 'nullable|string|max:255',
            'doctors.*.latitude' => 'nullable|numeric',
            'doctors.*.longitude' => 'nullable|numeric',
            'doctors.*.location' => 'nullable|array',
        ]);

        $synced = [];

        DB::transaction(function () use ($validated, &$synced) {
            foreach ($validated['doctors'] as $item) {
                $lat = isset($item['latitude']) && is_numeric($item['latitude']) ? (float) $item['latitude'] : null;
                $lng = isset($item['longitude']) && is_numeric($item['longitude']) ? (float) $item['longitude'] : null;

                $location = ($lat !== null && $lng !== null)
                    ? Doctor::pointGeoJson($lat, $lng)
                    : ($item['location'] ?? null);

                $doctor = Doctor::updateOrCreate(
                    ['uuid' => $item['uuid']],
                    [
                        'name' => $item['name'],
                        'email' => $item['email'] ?? null,
                        'phone' => $item['phone'] ?? null,
                        'specialty' => $item['specialty'] ?? null,
                        'qualification' => $item['qualification'] ?? null,
                        'town' => $item['town'] ?? null,
                        'area_id' => $item['area_id'] ?? null,
                        'address' => $item['address'] ?? null,
                        'clinic_name' => $item['clinic_name'] ?? null,
                        'latitude' => $lat,
                        'longitude' => $lng,
                        'location' => $location,
                        'status' => 'active',
                    ]
                );

                $synced[] = [
                    'uuid' => $doctor->uuid,
                    'server_id' => $doctor->id,
                    'name' => $doctor->name,
                    'status' => 'synced',
                ];
            }
        });

        return response()->json([
            'success' => true,
            'synced' => $synced,
        ]);
    }

    /**
     * Upload pending DCR entries queued locally.
     */
    public function syncDcrs(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'dcrs' => 'required|array',
            'dcrs.*.client_uuid' => 'required|string',
            'dcrs.*.date' => 'required|date_format:Y-m-d',
            'dcrs.*.doctor_id' => 'nullable|integer',
            'dcrs.*.doctor_uuid' => 'nullable|string',
            'dcrs.*.remarks' => 'nullable|string|max:1000',
            'dcrs.*.products' => 'nullable|array',
            'dcrs.*.products.*.product_id' => 'required|integer',
            'dcrs.*.products.*.quantity' => 'required|integer|min:1',
            'dcrs.*.promotional_inputs' => 'nullable|array',
            'dcrs.*.promotional_inputs.*.promotional_input_id' => 'required|integer',
            'dcrs.*.promotional_inputs.*.quantity' => 'required|integer|min:1',
        ]);

        $syncedUuids = [];

        DB::transaction(function () use ($validated, &$syncedUuids) {
            foreach ($validated['dcrs'] as $item) {
                $doctorId = $item['doctor_id'] ?? null;

                if (! $doctorId && ! empty($item['doctor_uuid'])) {
                    $doc = Doctor::where('uuid', $item['doctor_uuid'])->first();
                    if ($doc) {
                        $doctorId = $doc->id;
                    }
                }

                if (! $doctorId) {
                    continue;
                }

                $dcr = DCR::updateOrCreate(
                    ['uuid' => $item['client_uuid']],
                    [
                        'date' => $item['date'],
                        'doctor_id' => $doctorId,
                        'remarks' => $item['remarks'] ?? null,
                    ]
                );

                if (! empty($item['products'])) {
                    $dcr->sampleProducts()->delete();
                    foreach ($item['products'] as $prod) {
                        DCRProduct::create([
                            'dcr_id' => $dcr->id,
                            'product_id' => $prod['product_id'],
                            'quantity' => $prod['quantity'],
                        ]);
                    }
                }

                if (! empty($item['promotional_inputs'])) {
                    $dcr->promotionalInputs()->delete();
                    foreach ($item['promotional_inputs'] as $input) {
                        DCRPromotionalInput::create([
                            'dcr_id' => $dcr->id,
                            'promotional_input_id' => $input['promotional_input_id'],
                            'quantity' => $input['quantity'],
                        ]);
                    }
                }

                $syncedUuids[] = $item['client_uuid'];
            }
        });

        return response()->json([
            'success' => true,
            'synced_uuids' => $syncedUuids,
        ]);
    }
}
