<?php

namespace Fei\Service\Translate\Package;

use Fei\Service\Translate\Client\Translate;
use Fei\Service\Translate\Package\Config\TranslateParam;
use ObjectivePHP\Application\ApplicationInterface;

/**
 * Class TranslatePackage
 * @package E4P\Translate\Package
 */
class TranslatePackage
{
    const DEFAULT_IDENTIFIER = 'translate.client';

    /** @var string */
    protected $identifier;

    /** @var string */
    protected $class;

    /**
     * LoggerClientPackage constructor.
     * @param string $serviceIdentifier
     */
    public function __construct(string $serviceIdentifier = self::DEFAULT_IDENTIFIER, string $class = Translate::class)
    {
        $this->identifier = $serviceIdentifier;
        $this->class = $class;
    }

    public function __invoke(ApplicationInterface $app)
    {
        $params = $app->getConfig()->subset(TranslateParam::class);

        // Create translate directory
        if (!is_dir($params->get('translate_directory'))) {
            mkdir($params->get('translate_directory'), 0755);
        }

        $service = [
            'id' => 'translate.client',
            'class' => $this->class,
            'params' => [
                [Translate::OPTION_BASEURL => $params->get('base_url')],
                $params->get('translate_config')
            ],
            'setters' => [
                'setTransport' => $params->get('transport'),
            ]
        ];

        // setting the default language if it is set
        if ($params->get('translate_lang')) {
            $service['setters']['setLang'] = $params->get('translate_lang');
        }

        // setting the default namespace if it is set
        if ($params->get('translate_namespace')) {
            $service['setters']['setDomain'] = $params->get('translate_namespace');
        }

        $app->getServicesFactory()->registerService($service);
    }
}
