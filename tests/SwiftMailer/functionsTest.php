<?php

namespace Shippinno\Email\SwiftMailer;

use PHPUnit\Framework\TestCase;
use Swift_Message;

class functionsTest extends TestCase
{
    public function testAllowingNonRFCEmailAddress()
    {
        allow_non_rfc_email_address();
        $message = new Swift_Message;
        $message->setTo('email..@docomo.ne.jp');
        $this->assertTrue(true);
    }
}