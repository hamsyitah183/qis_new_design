<?php

namespace App\Http\Controllers;

use App\Models\DocumentRequirement;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    //
    function documentsList()
    {
        $document =  DocumentRequirement::where('is_active', 1)
                         ->where('module', 'user')
                         ->orderBy('id')
                         ->get();

        return response()->json([
            'status' => 200,
            'document' => $document
        ]);
    }
}
