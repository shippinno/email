<?php
declare(strict_types=1);

namespace Shippinno\Email\SwiftMailer;

use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\EmailValidation;

class NonRFCEmailValidator extends EmailValidator
{
    /**
     * {@inheritdoc}
     */
    public function isValid($email, EmailValidation $emailValidation)
    {
        if (substr_count($email, '@') < 1) {
            return false;
        }
        if (!preg_match('/^.+\@\S+\.\S+$/', $email)) {
            return false;
        }

        return true;
    }
}
