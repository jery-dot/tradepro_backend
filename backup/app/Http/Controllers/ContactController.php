<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactFormMail;

class ContactController extends Controller
{
    public function send(Request $request)
    {
        // 1. Validate the inputs based on your form
        $data = $request->validate([
            'user_name'  => 'required|string|max:255',
            'user_email' => 'required|email',
            'user_phone' => 'nullable|string',
            'subject'    => 'nullable|string',
            'message'    => 'required|string',
            'method'     => 'nullable|array',
            'privacy'    => 'accepted', // Ensures the privacy checkbox is checked
        ]);

        // 2. Send the email using AWS SES
        try {
            // Sends via AWS SES configured in your .env
            Mail::to('admin@tradepro.services')->send(new ContactFormMail($data));

            return response()->json(['message' => 'Success! Email sent.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'AWS SES Error: ' . $e->getMessage()], 500);
        }
    }
}
