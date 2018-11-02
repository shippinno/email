<?php
declare(strict_types=1);

namespace Shippinno\Email\SwiftMailer;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Shippinno\Email\EmailNotSentException;
use Shippinno\Email\SendEmail;
use Shippinno\Email\SmtpConfiguration;
use Swift_Attachment;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;
use Swift_TransportException;
use Tanigami\ValueObjects\Web\Email;
use Tanigami\ValueObjects\Web\EmailAddress;

class SwiftMailerSendEmail implements SendEmail
{
    use LoggerAwareTrait;

    /**
     * @var Swift_Mailer
     */
    private $defaultMailer;

    /**
     * @var bool
     */
    private $appliesSmtpConfiguration;

    /**
     * @param Swift_Mailer $defaultMailer
     * @param bool $appliesSmtpConfiguration
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        Swift_Mailer $defaultMailer,
        bool $appliesSmtpConfiguration,
        LoggerInterface $logger = null
    ) {
        $this->defaultMailer = $defaultMailer;
        $this->appliesSmtpConfiguration = $appliesSmtpConfiguration;
        $this->setLogger(is_null($logger) ? new NullLogger : $logger);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Email $email, SmtpConfiguration $smtpConfiguration = null): int
    {
        $mailer = $this->defaultMailer;
        if ($this->appliesSmtpConfiguration && !is_null($smtpConfiguration)) {
            $mailer = $this->smtpConfiguredMailer($smtpConfiguration);
        }
        $message = (new Swift_Message)
            ->setSubject($email->subject())
            ->setFrom($email->from()->emailAddress())
            ->setBody($email->body(), 'text/plain')
            ->setTo(array_map(function (EmailAddress $emailAddress) {
                return $emailAddress->emailAddress();
            }, $email->tos()))
            ->setCc(array_map(function (EmailAddress $emailAddress) {
                return $emailAddress->emailAddress();
            }, $email->ccs()))
            ->setBcc(array_map(function (EmailAddress $emailAddress) {
                return $emailAddress->emailAddress();
            }, $email->bccs()));
        foreach ($email->attachments() as $attachment) {
            $message->attach(
                new Swift_Attachment(
                    $attachment->content(),
                    $attachment->fileName(),
                    $attachment->mimeType()
                )
            );
        }
        $failedRecipients = [];
        try {
            $sent = $mailer->send($message, $failedRecipients);
            if ($sent !== $this->countRecipientsOfEmail($email)) {
                throw new EmailNotSentException($failedRecipients);
            }
            $this->logger->debug('An email was successfully sent.', [
                'to' => implode(', ', $message->getTo()),
                'subject' => $message->getSubject(),
                'body' => $message->getBody(),
            ]);
        } catch (Swift_TransportException $e) {
            throw new EmailNotSentException($failedRecipients, $e);
        }

        return $sent;
    }

    /**
     * @param Email $email
     * @return int
     */
    protected function countRecipientsOfEmail(Email $email): int
    {
        return count($email->tos()) + count($email->ccs()) + count($email->bccs());
    }

    /**
     * @param SmtpConfiguration $smtpConfiguration
     * @return Swift_Mailer
     */
    protected function smtpConfiguredMailer(SmtpConfiguration $smtpConfiguration): Swift_Mailer
    {
        return new Swift_Mailer(
            (new Swift_SmtpTransport($smtpConfiguration->host(), $smtpConfiguration->port()))
                ->setUsername($smtpConfiguration->username())
                ->setPassword($smtpConfiguration->password())
        );
    }
}
