<?php

namespace App\Http\Controllers\public\importPermit;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PermitApplicationController extends Controller
{
    public function show()
    {
        return view('pages.public.apply_permit');
    }
}
