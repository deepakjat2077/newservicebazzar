<?php

namespace App\Http\Libs;

use App\Jobs\SendEmailJob;
use App\Models\NotificationTemplate;
use App\Models\Setting;
use PHPMailer\PHPMailer\PHPMailer;

class SendMail
{
    public function sendMail($data)
    {
        $setting = Setting::first();

        if ($setting->email_queue == 1) {
            $this->queueMail($data);
        } else {
            $this->directMail($data);
        }
    }

    protected function queueMail($data)
    {
        SendEmailJob::dispatch($data)->delay(now()->addSeconds(3));
    }

    protected function directMail($data)
    {
        $this->sendMailProcess($data);
    }

    public function sendMailProcess($data)
    {
        $setting = Setting::first();
        $notification = NotificationTemplate::where('act', $data['template'] ?? null)->first();

        $name = $data['name'] ?? 'Subscriber';
        // The greeting name is substituted directly into an HTML email body;
        // escape it regardless of whether the caller already did so, since a
        // person's name should never itself contain markup.
        $safeName = htmlspecialchars((string) $name, ENT_QUOTES, 'UTF-8');

        if ($notification) {
            // Decode entities in the *operator-authored* template pieces only
            // (the admin-editable notification/site email templates), before
            // any caller-supplied `value` fields are substituted in. Doing
            // the decode after substitution would reverse any HTML-escaping
            // a caller applied to untrusted values (e.g. a guest's name),
            // turning safely-escaped input back into live markup.
            $template = html_entity_decode($notification->email_message);

            foreach ($data['value'] as $key => $value) {
                $template = str_replace('{:' . $key . ':}', $value, $template);
            }

            $userMessage = str_replace('{:name:}', $safeName, html_entity_decode($setting->email_template));
            $userMessage = str_replace('{:message:}', $template, $userMessage);
        } else {
            $userMessage = $data['value']['message'] ?? '';
        }

        $message = ['email' => $userMessage];

        if (array_key_exists('subject', $data)) {
            $subject = $data['subject'];
        } else {
            $subject = $notification->subject ?? 'Email Notification';
        }

        $emailSetting = $setting->mail;

        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = $emailSetting->host;
        $mail->SMTPAuth = true;
        $mail->Username = $emailSetting->username;
        $mail->Password = $emailSetting->password;

        if ($emailSetting->encryption == 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }

        $mail->Port = $emailSetting->port;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom($emailSetting->email_from, $setting->site_name);
        $mail->addAddress($data['email'], $name);
        $mail->addReplyTo($emailSetting->email_from, $setting->site_name);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message['email'];
        $mail->send();
    }
}