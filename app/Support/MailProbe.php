<?php

namespace App\Support;

use App\Models\Setting;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Sends one message and reports what the mail server actually said.
 *
 * Everything the site mails — the contact form, enquiry notifications, the
 * account verification link — goes out through one set of SMTP credentials on
 * the settings row. When those stop working every one of those turns into
 * "we could not send your message right now", and the reason is a line of SMTP
 * the application throws away: PHPMailer surfaces "SMTP Error: data not
 * accepted." while the server said something far more useful just before it.
 *
 * This keeps the conversation and hands it back, for the admin panel's test
 * button and the `mail:test` command both.
 */
class MailProbe
{
    /**
     * @return array{
     *     ok: bool,
     *     error: string|null,
     *     reply: string|null,
     *     hint: string|null,
     *     transcript: array<int, string>,
     *     config: array<string, string>
     * }
     */
    public static function send(string $to, ?string $subject = null, ?string $body = null): array
    {
        $setting = Setting::first();
        $config = $setting->mail ?? null;

        if (!$config || empty($config->host)) {
            return [
                'ok'         => false,
                'error'      => 'No SMTP settings saved. Fill them in under Settings > Email.',
                'reply'      => null,
                'hint'       => null,
                'transcript' => [],
                'config'     => [],
            ];
        }

        $summary = [
            'host'       => (string) $config->host,
            'port'       => (string) $config->port,
            'encryption' => (string) $config->encryption,
            'username'   => (string) $config->username,
            'from'       => (string) $config->email_from,
            'to'         => $to,
        ];

        $transcript = [];

        $mail = new PHPMailer(true);
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = function ($line) use (&$transcript) {
            $transcript[] = rtrim($line);
        };

        $mail->isSMTP();
        $mail->Host = $config->host;
        $mail->SMTPAuth = true;
        $mail->Username = $config->username;
        $mail->Password = $config->password;
        $mail->SMTPSecure = $config->encryption === 'ssl'
            ? PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = (int) $config->port;
        $mail->Timeout = 20;
        $mail->CharSet = 'UTF-8';

        try {
            $mail->setFrom($config->email_from, $setting->site_name ?? config('app.name'));
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject ?: 'SMTP test from ' . ($setting->site_name ?? config('app.name'));
            $mail->Body = $body ?: '<p>If you are reading this, the site can send mail.</p>';
            $mail->send();

            return [
                'ok'         => true,
                'error'      => null,
                'reply'      => null,
                'hint'       => null,
                'transcript' => $transcript,
                'config'     => $summary,
            ];
        } catch (\Throwable $exception) {
            $reply = self::lastServerReply($transcript);

            return [
                'ok'         => false,
                'error'      => $exception->getMessage(),
                'reply'      => $reply,
                'hint'       => self::hint($config, $reply),
                'transcript' => $transcript,
                'config'     => $summary,
            ];
        }
    }

    /** The server's own last word, which is where the refusal actually is. */
    protected static function lastServerReply(array $transcript): ?string
    {
        foreach (array_reverse($transcript) as $line) {
            if (str_contains($line, 'SERVER -> CLIENT:')) {
                $reply = trim(substr($line, strpos($line, 'SERVER -> CLIENT:') + 17));

                // "221 Bye" is the goodbye after the failure, not the failure.
                if ($reply !== '' && !str_starts_with($reply, '221')) {
                    return $reply;
                }
            }
        }

        return null;
    }

    /** Plain language for the handful of things that actually go wrong. */
    protected static function hint($config, ?string $reply): ?string
    {
        $port = (int) $config->port;
        $encryption = strtolower((string) $config->encryption);

        // The one that produces the strangest symptoms: implicit TLS spoken at
        // the port that expects STARTTLS, or the other way round. Worth saying
        // before anything else, because the server's own reply rarely names it.
        if ($port === 587 && $encryption === 'ssl') {
            return 'Port 587 expects TLS, not SSL. Either set Encryption to TLS, or change the port to 465.';
        }

        if ($port === 465 && $encryption === 'tls') {
            return 'Port 465 expects SSL, not TLS. Either set Encryption to SSL, or change the port to 587.';
        }

        if ($reply === null) {
            return 'Could not get a reply from the mail server — check the host and port, and that the hosting account allows outbound SMTP.';
        }

        if (str_starts_with($reply, '535')) {
            return 'The username or password is wrong. Re-enter the mailbox password under Settings > Email.';
        }

        if (str_starts_with($reply, '550') || str_starts_with($reply, '554')) {
            return 'The server refused the message — usually the From address is not that mailbox, the recipient was rejected, or a sending limit has been reached.';
        }

        return null;
    }
}
