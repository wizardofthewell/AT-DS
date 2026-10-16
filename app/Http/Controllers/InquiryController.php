<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use App\Models\SiteSetting;
use App\Notifications\NewInquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class InquiryController extends Controller
{
    public function store(Request $request)
    {
        // Honeypot: real visitors never see or fill this field.
        if (filled($request->input('website'))) {
            return back()->with('inquiry_sent', true);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'due_date' => ['nullable', 'date'],
            'service_interest' => ['nullable', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $inquiry = Inquiry::create($data + ['ip_address' => $request->ip()]);

        $recipient = SiteSetting::get('contact.notify_email') ?: SiteSetting::get('contact.email');

        if ($recipient) {
            try {
                Notification::route('mail', $recipient)->notify(new NewInquiry($inquiry));
            } catch (\Throwable $e) {
                // The inquiry is saved in the admin either way; don't fail the visitor's submission.
                Log::warning('Inquiry notification failed: '.$e->getMessage());
            }
        }

        return redirect()->to(url()->previous().'#contact')->with('inquiry_sent', true);
    }
}
