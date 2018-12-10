<?php
declare(strict_types=1);

namespace Shippinno\Email\SwiftMailer;

use Swift_Mime_Grammar;

class NonRFCMimeGrammer extends Swift_Mime_Grammar
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition($name)
    {
        switch ($name) {
            case 'addr-spec':
                return '.+\@\S+\.\S+';
            default:
                /** @noinspection PhpUnhandledExceptionInspection */
                return parent::getDefinition($name);
        }
    }
}
