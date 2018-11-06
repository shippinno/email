<?php
declare(strict_types=1);

namespace Shippinno\Email\SwiftMailer {

    use Swift_DependencyContainer;

    function allow_non_rfc_email_address()
    {
        $container = Swift_DependencyContainer::getInstance();
        $container
            ->register('email.validator')
            ->asSharedInstanceOf(NonRFCEmailValidator::class);
        $container
            ->register('mime.grammar')
            ->asSharedInstanceOf(NonRFCMimeGrammer::class);
    }
}
