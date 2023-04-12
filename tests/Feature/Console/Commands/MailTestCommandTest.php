<?php

namespace Tests\Feature\Console\Commands;

use App\Mail\MailerMail;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MailTestCommandTest extends TestCase
{
    /** @test */
    public function can_send_an_email()
    {
        Mail::fake();

        $this->artisan('mail:test rafael.camison@austrian.com');

        Mail::assertSent(MailerMail::class, function ($mail) {
            return $mail->to[0]['address'] === 'rafael.camison@austrian.com'
                && $mail->subject === 'OneUp TestMail';
        });

    }
}
