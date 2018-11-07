# Email

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/shippinno/email/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/shippinno/email/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/shippinno/email/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/shippinno/email/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/shippinno/email/badges/build.png?b=master)](https://scrutinizer-ci.com/g/shippinno/email/build-status/master)

## Installation

```sh
$ composer require shippinno/email
```

## Usage

Use a `SendEmail` to send an `Email`. It reattempts to send if `$maxReattempts` attribute is set.

```php
use Shippinno\Email\SwiftMailer\SwiftMailerSendEmail;
use Tanigami\ValueObjects\Web\Email;
use Swift_Mailer;

$sendEmail = new SwiftMailerSendEmail(new Swift_Mailer(...));
$sendEmail->execute(
    new Email(...),
    3 // max reattempts
);
```

### Dealing with non RFC email address

#### Swift Mailer

Swift Mailer rejects Non RFC compliant email addresses by default.

You can set a custom email validator and Mime grammer (for Swift Mailer 5.x compatibility) allowing non RFC email address (e.g. `email.@example.com`) by calling `allow_non_rfc_email_address();` function.

```php
use function Shippinno\Email\SwiftMailer\register_swift_non_rfc_email_validator;

allow_non_rfc_email_address();
(new Swift_Message)->setTo('email.@example.com'); // => OK
```

#### `EmailAddress` object

`EmailAddress` requires its value to be RFC compliant by default. You can have it soft validate by setting the second attribute of the constructor to `true`.

```php
new EmailAddress('email..@example.com') // => InvalidArgumentException
new EmailAddress('email..@example.com', true); // => OK
```
