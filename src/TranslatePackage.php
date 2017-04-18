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

    /**
     * LoggerClientPackage constructor.
     * @param string $serviceIdentifier
     */
    public function __construct(string $serviceIdentifier = self::DEFAULT_IDENTIFIER)
    {
        $this->identifier = $serviceIdentifier;
    }

    public function __invoke(ApplicationInterface $app)
    {
        $params = $app->getConfig()->subset(TranslateParam::class);

        // Create translate directory
        if (!is_dir($params->get('translate_directory'))) {
            mkdir($params->get('translate_directory'), 0755);
        }

        $app->getServicesFactory()->registerService(
            [
                'id' => 'translate.client',
                'class' => Translate::class,
                'params' => [
                    [Translate::OPTION_BASEURL => $params->get('base_url')],
                    $params->get('translate_config')
                ],
                'setters' => [
                    'setTransport' => $params->get('transport')
                ]
            ]
        );
    }
}
