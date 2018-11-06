<?php
declare(strict_types=1);

namespace Shippinno\Email\SwiftMailer;

use Swift_Mime_Grammar;

/**
 * @see https://gist.github.com/basuke/16ad5c07fba3f029e729
 */
class NonRFCMimeGrammer extends Swift_Mime_Grammar
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition($name)
    {
        switch ($name) {
            case 'addr-spec':
                /** @noinspection PhpUnhandledExceptionInspection */
                $spec = parent::getDefinition($name);
                $fuzzy_domains = array(
                    'docomo\.ne\.jp',
                    'ezweb\.ne\.jp',
                );
                $atext = $this->getDefinition('atext');
                $CFWS = $this->getDefinition('CFWS');
                $invalid_dot_atom_text = '(?:\.*'. $atext. '+'. '(\.+'. $atext. '+)*\.*)';
                $invalid_dot_atom = '(?:'. $CFWS. '?'. $invalid_dot_atom_text. '+'. $CFWS. '?)';
                $quoted_string = $this->getDefinition('quoted-string');
                $local_part = '(?:'. $invalid_dot_atom. '|'. $quoted_string. ')';
                $domain_part = '(?:'. implode('|', $fuzzy_domains). ')';
                $spec = '(?:'. $spec. '|'. $local_part. '@'. $domain_part. ')';

                return $spec;
            default:
                /** @noinspection PhpUnhandledExceptionInspection */
                return parent::getDefinition($name);
        }
    }
}