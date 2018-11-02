<?php
declare(strict_types=1);

namespace Shippinno\Email;

use Exception;
use Throwable;

class EmailNotSentException extends Exception
{
    /**
     * @var array
     */
    private $recipients;

    /**
     * @param string[] $recipients
     * @param Throwable|null $previous
     */
    public function __construct(array $recipients = [], Throwable $previous = null)
    {
        $this->recipients = $recipients;
        $message = 'Failed to send email.';
        if (count($recipients) > 0) {
            $message .= ' Recipients: ' . implode(', ', $this->recipients());
        }
        parent::__construct($message, 0, $previous);
    }

    /**
     * @return string[]
     */
    public function recipients(): array
    {
        return $this->recipients;
    }
}
