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

$sendEmail = new SwiftMailerSendEmail();
$sendEmail->execute(
    new Email(...),
    3 // max reattempts
);
```

### Let Swift Mailer allow non RFC email address

You can set a custom email validator allowing non RFC email address (e.g. `email..@example.com`) by calling `init_swift_with_non_rfc_email_validator()` function.

```php
use function Shippinno\Email\SwiftMailer\init_swift_with_non_rfc_email_validator;

init_swift_with_non_rfc_email_validator();
```
