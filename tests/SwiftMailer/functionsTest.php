<?php

namespace Shippinno\Email\SwiftMailer;

use PHPUnit\Framework\TestCase;
use Swift_Message;

class functionsTest extends TestCase
{
    public function testAllowingNonRFCEmailAddress()
    {
        register_swift_non_rfc_email_validator();
        $message = new Swift_Message;
        $message->setTo('email..@example.com');
        $this->assertTrue(true);
    }
}