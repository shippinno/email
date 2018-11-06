<?php
declare(strict_types=1);

namespace Shippinno\Email\SwiftMailer {

    use Swift_DependencyContainer;

    function register_swift_non_rfc_email_validator()
    {
        Swift_DependencyContainer::getInstance()
            ->register('email.validator')
            ->asSharedInstanceOf(NonRFCEmailValidator::class);
    }
}
