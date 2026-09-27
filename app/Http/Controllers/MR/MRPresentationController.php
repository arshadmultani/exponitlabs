<?php

namespace App\Http\Controllers\MR;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MRPresentationController extends Controller
{
    public function index(Request $request): View
    {
        $doctorUuid = $request->query('doctor_uuid') ?? $request->query('doctor');
        $doctor = null;

        if ($doctorUuid) {
            $doctor = Doctor::where('uuid', $doctorUuid)
                ->orWhere('id', $doctorUuid)
                ->first();
        }

        $products = Product::active()->get();

        return view('mr.presentation.index', [
            'doctor' => $doctor,
            'doctorUuid' => $doctorUuid,
            'products' => $products,
        ]);
    }
}
