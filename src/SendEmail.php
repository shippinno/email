<?php
declare(strict_types=1);

namespace Shippinno\Email;

use Tanigami\ValueObjects\Web\Email;

interface SendEmail
{
    /**
     * @param Email $email
     * @param SmtpConfiguration|null $smtpConfiguration
     * @return int
     * @throws EmailNotSentException
     */
    public function execute(Email $email, SmtpConfiguration $smtpConfiguration = null): int;
}
