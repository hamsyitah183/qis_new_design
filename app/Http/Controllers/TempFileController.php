<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TempFileController extends Controller
{
    //
    public function upload(Request $request)
    {
        if (!$request->hasFile('file')) {
            return response()->json(['status' => 'error', 'message' => 'No file uploaded'], 400);
        }

        $file = $request->file('file');
        $tempName = uniqid() . "_" . $file->getClientOriginalName();
        $path = $file->storeAs('public/temp', $tempName);

        return response()->json([
            'status' => 'success',
            'original_name' => $file->getClientOriginalName(),
            'temp_name' => $tempName,
            'temp_path' => "storage/temp/$tempName",
        ]);
    }
}
