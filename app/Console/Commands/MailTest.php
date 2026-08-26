<?php

namespace App\Console\Commands;

use App\Support\MailProbe;
use Illuminate\Console\Command;

/**
 * Says out loud why mail is not going out.
 *
 *   php artisan mail:test
 *   php artisan mail:test --to=someone@example.com
 *
 * The admin panel's "Test Mail" button runs the same check (EmailController).
 */
class MailTest extends Command
{
    protected $signature = 'mail:test {--to= : Where to send the test message (defaults to the From address)}';

    protected $description = 'Check the site SMTP settings and report exactly what the mail server says';

    public function handle(): int
    {
        $setting = \App\Models\Setting::first();
        $to = $this->option('to') ?: ($setting->mail->email_from ?? null);

        if (!$to) {
            $this->error('No recipient. Pass --to=someone@example.com');

            return self::FAILURE;
        }

        $result = MailProbe::send($to);

        foreach ($result['config'] as $key => $value) {
            $this->line(str_pad($key, 11) . ': ' . $value);
        }

        $this->newLine();

        if ($result['ok']) {
            $this->info('SENT. The settings work — check ' . $to . '.');

            return self::SUCCESS;
        }

        $this->error('FAILED: ' . $result['error']);

        if ($result['reply']) {
            $this->line('Server said: ' . $result['reply']);
        }

        if ($result['hint']) {
            $this->newLine();
            $this->warn($result['hint']);
        }

        $this->newLine();
        $this->line('Conversation (last lines):');

        foreach (array_slice($result['transcript'], -12) as $line) {
            $this->line('  ' . $line);
        }

        return self::FAILURE;
    }
}
