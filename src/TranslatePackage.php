<?php

namespace Fei\Service\Translate\Package;

use Fei\Service\Connect\Client\Connect;
use Fei\Service\Connect\Common\Entity\User;
use Fei\Service\Translate\Client\Translate;
use Fei\Service\Translate\Package\Config\TranslateParam;
use ObjectivePHP\Application\ApplicationInterface;
use ObjectivePHP\Cli\Config\CliCommand;

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

    /** @var string */
    protected $connectClientIdentifier = 'connect.client';

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
        $app->getConfig()->import(new CliCommand(new TranslateCli()));
        // Create translate directory
        if (!is_dir($params->get('translate_directory'))) {
            mkdir($params->get('translate_directory'), 0755);
        }

        $service = [
            'id' => $this->identifier,
            'class' => $this->class,
            'params' => [
                [Translate::OPTION_BASEURL => $params->get('base_url')],
                $params->get('translate_config')
            ],
            'setters' => [
                'setTransport' => $params->get('transport'),
            ]
        ];

        if ($app->getServicesFactory()->has($this->connectClientIdentifier)) {
            /** @var Connect $client */
            $client = $app->getServicesFactory()->get($this->connectClientIdentifier);
            if ($client->getUser() instanceof User) {
                $service['setters']['setLang'] = $client->getUser()->getLanguage();
            }
        } else if ($params->get('translate_lang')) {
            $service['setters']['setLang'] = $params->get('translate_lang');
        }

        // setting the default namespace if it is set
        if ($params->get('translate_namespace')) {
            $service['setters']['setDomain'] = $params->get('translate_namespace');
        }

        $app->getServicesFactory()->registerService($service);
    }

    /**
     * Get ConnectClientIdentifier
     *
     * @return string
     */
    public function getConnectClientIdentifier(): string
    {
        return $this->connectClientIdentifier;
    }

    /**
     * Set ConnectClientIdentifier
     *
     * @param string $connectClientIdentifier
     *
     * @return $this
     */
    public function setConnectClientIdentifier(string $connectClientIdentifier)
    {
        $this->connectClientIdentifier = $connectClientIdentifier;

        return $this;
    }
}
