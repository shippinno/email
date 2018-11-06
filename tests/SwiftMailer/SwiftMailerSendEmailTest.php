<?php

namespace Shippinno\Email\SwiftMailer;

use Exception;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Shippinno\Email\SmtpConfiguration;
use Swift_DependencyContainer;
use Swift_Mailer;
use Tanigami\ValueObjects\Web\Email;
use Tanigami\ValueObjects\Web\EmailAddress;
use Tanigami\ValueObjects\Web\EmailAttachment;

class SwiftMailerSendEmailTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testSendFullEmail()
    {
        $email = new Email(
            'SUBJECT',
            'BODY',
            new EmailAddress('from@example.com'),
            [new EmailAddress('to1@example.com'), new EmailAddress('to2@example.com')],
            [new EmailAddress('cc1@example.com'), new EmailAddress('cc2@example.com')],
            [new EmailAddress('bcc1@example.com'), new EmailAddress('bcc2@example.com')],
            [new EmailAttachment('c,s,v,1', 'file.csv'), new EmailAttachment('<p>Hello</p>', 'file.html')]
        );
        $mailer = Mockery::mock(Swift_Mailer::class);
        $mailer
            ->shouldReceive('send')
            ->withArgs(function (\Swift_Message $message, array $a) {
                return
                    $message->getSubject() === 'SUBJECT' &&
                    $message->getBody() === 'BODY' &&
                    $message->getFrom() == ['from@example.com' => null] &&
                    $message->getTo() == ['to1@example.com' => null, 'to2@example.com' => null] &&
                    $message->getCc() == ['cc1@example.com' => null, 'cc2@example.com' => null] &&
                    $message->getBcc() == ['bcc1@example.com' => null, 'bcc2@example.com' => null] &&
                    $message->getChildren()[0]->getBody() === 'c,s,v,1' &&
                    $message->getChildren()[1]->getBody() === '<p>Hello</p>';
            })
            ->once()
            ->andReturn(6);
        $sendEmail = new SwiftMailerSendEmail($mailer);
        $sendEmail->execute($email);
    }

    /**
     * @expectedException \Shippinno\Email\EmailNotSentException
     * @expectedExceptionMessage
     */
    public function testItThrowsExceptionIfErrorOccurs()
    {
        $email = new Email(
            'subject',
            'body',
            new EmailAddress('from@example.com'),
            [new EmailAddress('to@example.com')]
        );
        $mailer = Mockery::mock(Swift_Mailer::class);
        $mailer
            ->shouldReceive('send')
            ->once()
            ->andThrow(new Exception);
        $sendEmail = new SwiftMailerSendEmail($mailer);
        $sendEmail->execute($email);
    }

    public function testItCreatesSmtpConfiguredMailer()
    {
        $sendEmail = new SwiftMailerSendEmail(Mockery::mock(Swift_Mailer::class), false);
        $class = new ReflectionClass($sendEmail);
        $method = $class->getMethod('smtpConfiguredMailer');
        $method->setAccessible(true);
        $mailer = $method->invokeArgs(
            $sendEmail,
            [new SmtpConfiguration('host.com', 123, 'username', 'password')]
        );
        $this->assertSame('host.com', $mailer->getTransport()->getHost());
        $this->assertSame(123, $mailer->getTransport()->getPort());
        $this->assertSame('username', $mailer->getTransport()->getUsername());
        $this->assertSame('password', $mailer->getTransport()->getPassword());
    }
}