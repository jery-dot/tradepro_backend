<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FileUploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $file = $request->file('file');

        // Original file name
        $originalName = $file->getClientOriginalName();

        // Move file
        $file->move(public_path('uploads'), $originalName);

        return response()->json([
            'success' => true,
            'file_name' => $originalName,
            'file_url' => asset('uploads/'.$originalName),
        ]);
    }
}
