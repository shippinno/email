<?php

namespace Shippinno\Email\SwiftMailer;

use Egulias\EmailValidator\Validation\EmailValidation;
use Mockery;
use PHPUnit\Framework\TestCase;

class NonRFCEmailValidatorTest extends TestCase
{
    public function testItAllowsNonRFCConpliantEmailAddress()
    {
        $validation = Mockery::mock(EmailValidation::class);
        $validator = new NonRFCEmailValidator;
        $this->assertTrue($validator->isValid('doubledots..@exapmle.com', $validation));
    }

    public function testItDisallowsTotallyWrongEmailAddress()
    {
        $validation = Mockery::mock(EmailValidation::class);
        $validator = new NonRFCEmailValidator;
        $this->assertFalse($validator->isValid('ThisIsNotEmailAddressAtAll', $validation));
    }

    public function testItDisallowsTotallyWrongEmailAddress2()
    {
        $validation = Mockery::mock(EmailValidation::class);
        $validator = new NonRFCEmailValidator;
        $this->assertFalse($validator->isValid('ThisIsNot@EmailAddress@AtAll', $validation));
    }
}
