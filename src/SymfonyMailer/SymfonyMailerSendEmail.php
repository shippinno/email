<?php
declare(strict_types=1);

namespace Shippinno\Email\SymfonyMailer;

use Psr\Log\LoggerInterface;
use Shippinno\Email\EmailNotSentException;
use Shippinno\Email\SendEmail;
use Shippinno\Email\SmtpConfiguration;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Address;
use Tanigami\ValueObjects\Web\Email;
use Tanigami\ValueObjects\Web\EmailAddress;
use Throwable;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email as MimeEmail;

class SymfonyMailerSendEmail extends SendEmail
{
    /**
     * @var Mailer
     */
    private $defaultMailer;

    /**
     * @var bool
     */
    private $ignoresSmtpConfiguration;

    /**
     * @param Mailer $defaultMailer
     * @param bool $ignoresSmtpConfiguration
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        Mailer $defaultMailer,
        bool $ignoresSmtpConfiguration = false,
        LoggerInterface $logger = null
    ) {
        $this->defaultMailer = $defaultMailer;
        $this->ignoresSmtpConfiguration = $ignoresSmtpConfiguration;
        parent::__construct($logger);
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecute(Email $email, SmtpConfiguration $smtpConfiguration = null): int
    {
        $mailer = $this->defaultMailer;
        if (false === $this->ignoresSmtpConfiguration && null !== $smtpConfiguration) {
            $mailer = $this->smtpConfiguredMailer($smtpConfiguration);
        }
        $message = (new MimeEmail)
            ->subject($email->subject())
            ->from($email->from()->emailAddress())
            ->text($email->body())
            ->to(...array_map(function (EmailAddress $emailAddress) {
                return $emailAddress->emailAddress();
            }, $email->tos()))
            ->cc(...array_map(function (EmailAddress $emailAddress) {
                return $emailAddress->emailAddress();
            }, $email->ccs()))
            ->bcc(...array_map(function (EmailAddress $emailAddress) {
                return $emailAddress->emailAddress();
            }, $email->bccs()));
        foreach ($email->attachments() as $attachment) {
            $message->attach($attachment->content(), $attachment->fileName(), $attachment->mimeType());
        }

        try {
            $mailer->send($message);
            $this->logger->debug('An email was successfully sent.', [
                'to' => implode(', ', array_map(function (Address $address) {
                    return $address->toString();
                }, $message->getTo())),
                'subject' => $message->getSubject(),
                'body' => $message->getBody(),
            ]);

            return $this->countRecipientsOfEmail($email);
        } catch (Throwable $e) {
            throw new EmailNotSentException([$e->getMessage()], $e);
        }
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
     * @return Mailer
     */
    protected function smtpConfiguredMailer(SmtpConfiguration $smtpConfiguration): Mailer
    {
        return new Mailer(
            (new EsmtpTransport($smtpConfiguration->host(), $smtpConfiguration->port()))
                ->setUsername($smtpConfiguration->username())
                ->setPassword($smtpConfiguration->password())
        );
    }
}
