<?php

namespace App\Http\Controllers\MR;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Contracts\View\View;

class MRDoctorController extends Controller
{
    public function index(): View
    {
        return view('mr.doctors.index');
    }

    public function create(): View
    {
        return view('mr.doctors.create');
    }

    public function show(string $uuid): View
    {
        $doctor = Doctor::with([
            'area.headquarter',
            'dcrs' => fn ($q) => $q->orderBy('date', 'desc'),
            'dcrs.sampleProducts.product',
            'dcrs.promotionalInputs.promotionalInput',
        ])
            ->where('uuid', $uuid)
            ->orWhere('id', $uuid)
            ->first();

        return view('mr.doctors.show', [
            'uuid' => $uuid,
            'doctor' => $doctor,
        ]);
    }
}
