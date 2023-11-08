<?php

namespace Shippinno\Email;

use PHPUnit\Framework\TestCase;
use Tanigami\ValueObjects\Web\Email;
use Tanigami\ValueObjects\Web\EmailAddress;

class SendEmailTest extends TestCase
{
    /** @test */
    public function testItReattempts()
    {
        $email = new Email(
            'subject',
            'body',
            new EmailAddress('from@example.com'),
            [new EmailAddress('to@example.com')]
        );
        $sendEmail = new FailingSendEmail;
        try {
            $sendEmail->execute($email, 5);
        } catch (EmailNotSentException $e) {
            $this->assertSame(5, $sendEmail->attempts);
        }
    }
}

class FailingSendEmail extends SendEmail
{
    public $attempts = 0;

    protected function doExecute(Email $email, SmtpConfiguration $smtpConfiguration = null): int
    {
        $this->attempts += 1;
        throw new EmailNotSentException;
    }
}
