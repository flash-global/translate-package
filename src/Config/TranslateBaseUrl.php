<?php

namespace Fei\Service\Translate\Package\Config;

use ObjectivePHP\Config\SingleValueDirectiveGroup;

/**
 * Class TranslateBaseUrl
 */
class TranslateBaseUrl extends SingleValueDirectiveGroup
{
    /**
     * TranslateBaseUrl constructor.
     *
     * @BaseUrl string $value
     */
    public function __construct(string $value = '')
    {
        parent::__construct($value);
    }

    /**
     * Set the Base Url used by the client
     *
     * @BaseUrl string $baseUrl
     *
     * @return TranslateBaseUrl
     */
    public function setBaseUrl(string $baseUrl) : self
    {
        $this->value = $baseUrl;

        return $this;
    }
}
