<?php
declare(strict_types=1);

namespace Shippinno\Email;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Tanigami\ValueObjects\Web\Email;
use Throwable;

abstract class SendEmail
{
    use LoggerAwareTrait;

    /**
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        LoggerInterface $logger = null
    ) {
        $this->setLogger(is_null($logger) ? new NullLogger : $logger);
    }

    /**
     * @param Email $email
     * @param int $maxAttempts
     * @param SmtpConfiguration|null $smtpConfiguration
     * @return int
     * @throws EmailNotSentException
     * @throws Throwable
     */
    public function execute(Email $email, int $maxAttempts = 1, SmtpConfiguration $smtpConfiguration = null): int
    {
        for ($i = 1; $i <= $maxAttempts; $i++) {
            try {
                return $this->doExecute($email, $smtpConfiguration);
            } catch (EmailNotSentException|Throwable $e) {
                if ($i < $maxAttempts) {
                    continue;
                }
                $this->logger->debug(sprintf('Gave up sending an email after reattempting %d times.', $i), [
                    'subject' => $email->subject(),
                    'exception' => $e,
                ]);
                throw $e;
            }
        }
    }

    /**
     * @param Email $email
     * @param SmtpConfiguration|null $smtpConfiguration
     * @return int
     * @throws EmailNotSentException
     */
    abstract protected function doExecute(Email $email, SmtpConfiguration $smtpConfiguration = null);
}
