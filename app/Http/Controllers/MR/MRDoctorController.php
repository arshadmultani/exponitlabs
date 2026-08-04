<?php

namespace App\Http\Controllers\MR;

use App\Http\Controllers\Controller;
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
        return view('mr.doctors.show', ['uuid' => $uuid]);
    }
}
