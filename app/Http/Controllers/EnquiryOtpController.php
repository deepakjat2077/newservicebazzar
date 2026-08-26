<?php

namespace App\Http\Controllers;

use App\Rules\IndianMobile;
use App\Services\WhatsappSender;
use App\Support\MobileNumber;
use App\Support\OtpGate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * The two endpoints the enquiry forms call before they submit: send a code to
 * the number typed into the form, then check it. Nothing here writes an
 * enquiry — it only decides whether OtpGate will let one through.
 *
 * @see \App\Support\OtpGate
 */
class EnquiryOtpController extends Controller
{
    public function send(Request $request, WhatsappSender $whatsapp)
    {
        $request->validate([
            'phone' => ['required', 'string', 'max:20', new IndianMobile()],
        ]);

        $phone = $request->phone;

        // Already proven in this session (or signed in with this number), so
        // the form has nothing to ask and can submit straight away.
        if (OtpGate::isVerified($phone)) {
            return response()->json([
                'verified' => true,
                'message'  => 'Mobile number already verified.',
            ]);
        }

        $wait = OtpGate::cooldownRemaining($phone);

        if ($wait > 0) {
            return response()->json([
                'sent'    => true,
                'message' => 'An OTP is already on its way. You can ask for a new one in ' . $wait . 's.',
            ]);
        }

        if (!OtpGate::withinSendBudget()) {
            return response()->json([
                'message' => 'Too many OTP requests. Please try again later.',
            ], 429);
        }

        $templateId = config('services.gupshup.otp_template_id');

        if (!$templateId || !$whatsapp->isConfigured()) {
            Log::error('Enquiry OTP cannot be sent: Gupshup is not configured', [
                'template_id' => $templateId,
            ]);

            return response()->json([
                'message' => 'OTP service is unavailable right now. Please try again later.',
            ], 503);
        }

        $code = (string) verification_code(6);

        $message = $whatsapp->sendTemplate(
            MobileNumber::destination($phone),
            $templateId,
            [$code],
            [
                'contact_name' => $request->input('name'),
                // Never the code itself: whatsapp_messages is readable from
                // the admin panel, and a logged OTP is one anybody with that
                // screen could use to submit an enquiry as this visitor.
                'log_body'     => 'Enquiry OTP (code not logged)',
            ]
        );

        if ($message->status === 'failed') {
            return response()->json([
                'message' => 'Could not send the OTP right now. Please try again in a moment.',
            ], 502);
        }

        OtpGate::challenge($phone, $code);

        return response()->json([
            'sent'    => true,
            'message' => 'OTP sent on WhatsApp to ' . MobileNumber::national($phone) . '.',
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'string', 'max:20', new IndianMobile()],
            'otp'   => ['required', 'digits:6'],
        ]);

        $result = OtpGate::attempt($request->phone, (string) $request->otp);

        if ($result === 'ok') {
            return response()->json([
                'verified' => true,
                'message'  => 'Mobile number verified.',
            ]);
        }

        $messages = [
            'none'     => 'No OTP is pending for this number. Please request a new one.',
            'expired'  => 'That OTP has expired. Please request a new one.',
            'too_many' => 'Too many wrong attempts. Please request a new OTP.',
            'invalid'  => 'Incorrect OTP. Attempts left: ' . OtpGate::attemptsLeft($request->phone) . '.',
        ];

        return response()->json([
            'message' => $messages[$result] ?? 'Could not verify the OTP.',
        ], 422);
    }
}
