<?php
declare(strict_types=1);

namespace Shippinno\Email\SwiftMailer;

use Swift;
use Swift_DependencyContainer;

function initSwiftWithNonRFCEmailValidator()
{
    Swift::init(function () {
        Swift_DependencyContainer::getInstance()
            ->register('email.validator')
            ->asSharedInstanceOf(NonRFCEmailValidator::class);
    });
}