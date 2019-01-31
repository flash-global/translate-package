<?php

namespace Fei\Service\Translate\Package\Config;

use ObjectivePHP\Config\SingleValueDirective;

/**
 * Class TranslateAuthorization
 *
 * @package ObjectivePHP\Package\Translate\Config
 */
class TranslateAuthorization extends SingleValueDirective
{
    public function __construct(string $value = '')
    {
        parent::__construct($value);
    }

    /**
     * Set theAuthorization used by the client
     *
     * @param string $authorization
     *
     * @return TranslateAuthorization
     */
    public function setAuthorization(string $authorization) : self
    {
        $this->value = $authorization;

        return $this;
    }
}