<?php

namespace App\Http\Controllers\MR;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class MRDcrController extends Controller
{
    public function index(): View
    {
        return view('mr.dcr');
    }

    public function history(): View
    {
        return view('mr.dcrs.index');
    }
}
