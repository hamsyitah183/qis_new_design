<?php

namespace App\Http\Controllers\internal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MiscController extends Controller
{
    
    public function showcontrolpanel()
    {
        return view('pages.internal.misc.control_panel');
    }

    
}
