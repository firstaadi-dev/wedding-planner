<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendResendHelloEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resend:send-hello {to=firstaadip16@gmail.com}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a Hello World email using the Resend API';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $apiKey = (string) config('services.resend.key', config('services.resend.api_key', 're_xxxxxxxxx'));

        if ($apiKey === 're_xxxxxxxxx') {
            $this->error('Replace `re_xxxxxxxxx` with your real Resend API key in `RESEND_API_KEY`.');

            return 1;
        }

        if (!class_exists('Resend')) {
            $this->error('Resend SDK not found. Install it with Composer before running this command.');

            return 1;
        }

        $resend = \Resend::client($apiKey);

        $response = $resend->emails->send([
            'from' => 'onboarding@resend.dev',
            'to' => $this->argument('to'),
            'subject' => 'Hello World',
            'html' => '<p>Congrats on sending your <strong>first email</strong>!</p>',
        ]);

        $this->info('Resend request sent.');
        $this->line(json_encode($response));

        return 0;
    }
}
