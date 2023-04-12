<?php

namespace App\Console\Commands;

use App\Mail\MailerMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class MailTestCommand extends Command
{
    protected $signature = 'mail:test {to} {--subject=}';

    protected $description = 'Command description';

    public function handle(): void
    {
        $data = $this->validate();

        $mail = new MailerMail();
        $mail->subject = data_get($data, 'subject') ?? 'OneUp Test-Mail';

        Mail::to(data_get($data, 'to'))
            ->send($mail);
    }

    protected function validate(): array
    {
        $data = [
            'to' => $this->argument('to'),
            'subject' => $this->option('subject') ?? 'OneUp TestMail',
        ];
        return Validator::validate($data,
            [
                'to' => 'required|email',
                'subject' => 'required|string',
            ]
        );
    }
}
