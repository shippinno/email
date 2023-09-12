<?php
declare(strict_types=1);

namespace Shippinno\Email\SymfonyMailer;

use Psr\Log\LoggerInterface;
use Shippinno\Email\EmailNotSentException;
use Shippinno\Email\SendEmail;
use Shippinno\Email\SmtpConfiguration;
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
    protected function doExecute(Email $email, SmtpConfiguration $smtpConfiguration = null)
    {
        $mailer = $this->defaultMailer;
        // if (!$this->ignoresSmtpConfiguration && !is_null($smtpConfiguration)) {
        //     $mailer = $this->smtpConfiguredMailer($smtpConfiguration);
        // }
        $message = (new MimeEmail)
            ->subject($email->subject())
            ->from($email->from()->emailAddress())
            ->text($email->body(), 'text/plain');

        try {
            $sent = $mailer->send($message);
            $this->logger->debug('An email was successfully sent.', [
                'to' => implode(', ', $message->getTo()),
                'subject' => $message->getSubject(),
                'body' => $message->getBody(),
            ]);
        } catch (Throwable $e) {
            throw new EmailNotSentException();
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
     * @return Mailer
     */
    // protected function smtpConfiguredMailer(SmtpConfiguration $smtpConfiguration): Mailer
    // {
    //     return new Mailer(
    //         (new Swift_SmtpTransport($smtpConfiguration->host(), $smtpConfiguration->port()))
    //             ->setUsername($smtpConfiguration->username())
    //             ->setPassword($smtpConfiguration->password())
    //     );
    // }
}
