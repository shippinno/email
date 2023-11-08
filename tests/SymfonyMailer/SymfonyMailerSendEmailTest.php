<?php

namespace Shippinno\Email\SymfonyMailer;

use Exception;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Shippinno\Email\SmtpConfiguration;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email as MimeEmail;
use Tanigami\ValueObjects\Web\Email;
use Tanigami\ValueObjects\Web\EmailAddress;
use Tanigami\ValueObjects\Web\EmailAttachment;

class SymfonyMailerSendEmailTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /** @test */
    public function testSendFullEmail()
    {
        /*
         * テストをする場合、 mock が final クラスをモックできないため、Mailer クラスの final を一時的に取って実行すること。
         */

        $email = new Email(
            'SUBJECT',
            'BODY',
            new EmailAddress('from@example.com'),
            [new EmailAddress('to1@example.com'), new EmailAddress('to2@example.com')],
            [new EmailAddress('cc1@example.com'), new EmailAddress('cc2@example.com')],
            [new EmailAddress('bcc1@example.com'), new EmailAddress('bcc2@example.com')],
            [new EmailAttachment('c,s,v,1', 'file.csv'), new EmailAttachment('<p>Hello</p>', 'file.html')]
        );

        $mailer = Mockery::mock(Mailer::class);
        $mailer
            ->shouldReceive('send')
            ->withArgs(function (MimeEmail $message) {
                $subject = $message->getSubject();
                $body = $message->getTextBody();
                $from = $message->getFrom();
                $to = $message->getTo();
                $cc = $message->getCc();
                $bcc = $message->getBcc();
                $attachments = $message->getAttachments();

                return
                    $subject === 'SUBJECT'
                    && $body === 'BODY'
                    && $from == [Address::create('from@example.com')]
                    && $to == [Address::create('to1@example.com'), Address::create('to2@example.com')]
                    && $cc == [Address::create('cc1@example.com'), Address::create('cc2@example.com')]
                    && $bcc == [Address::create('bcc1@example.com'), Address::create('bcc2@example.com')]
                    && $attachments[0]->getBody() === 'c,s,v,1'
                    && $attachments[1]->getBody() === '<p>Hello</p>';
            })
            ->once()
            ->andReturn(count(array_merge($email->tos(), $email->ccs(), $email->bccs())));
        $sendEmail = new SymfonyMailerSendEmail($mailer);
        $sendEmail->execute($email);
    }

    /** @test */
    public function testItThrowsExceptionIfErrorOccurs()
    {
        $this->expectException(\Shippinno\Email\EmailNotSentException::class);
        $email = new Email(
            'SUBJECT',
            'BODY',
            new EmailAddress('from@example.com'),
            [new EmailAddress('to@example.com')]
        );
        $mailer = Mockery::mock(Mailer::class);
        $mailer
            ->shouldReceive('send')
            ->once()
            ->andThrow(new Exception);
        $sendEmail = new SymfonyMailerSendEmail($mailer);
        $sendEmail->execute($email);
    }

    /** @test */
    public function testItCreatesSmtpConfiguredMailer()
    {
        $mailer = Mockery::mock(Mailer::class);
        $sendEmail = new SymfonyMailerSendEmail($mailer, false);
        $class = new ReflectionClass(get_class($sendEmail));
        $method = $class->getMethod('smtpConfiguredMailer');
        $method->setAccessible(true);
        $mailer = $method->invokeArgs(
            $sendEmail,
            [new SmtpConfiguration('host.com', 123, 'username', 'password')]
        );

        $oReflectionClass = new ReflectionClass($mailer);
        $property = $oReflectionClass->getProperty('transport');
        $property->setAccessible(true);
        /** @var EsmtpTransport $transport */
        $transport = $property->getValue($mailer);

        $this->assertSame('host.com', $transport->getStream()->getHost());
        $this->assertSame(123, $transport->getStream()->getPort());
        $this->assertSame('username', $transport->getUsername());
        $this->assertSame('password', $transport->getPassword());
    }

    /** @test */
    public function testAllowingNonRFCEmailAddress()
    {
        $message = new MimeEmail;
        $message->to('email..@docomo.ne.jp');
        $this->assertTrue(true);
    }
}
