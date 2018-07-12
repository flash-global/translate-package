<?php

namespace Fei\Service\Translate\Package;

use Fei\ApiClient\Transport\BasicTransport;
use Fei\ApiClient\Transport\SyncTransportInterface;
use Fei\Service\Connect\Client\Connect;
use Fei\Service\Connect\Common\Entity\User;
use Fei\Service\Translate\Client\Translate;
use Fei\Service\Translate\Package\Config\TranslateParam;
use ObjectivePHP\Application\ApplicationInterface;
use ObjectivePHP\Cli\Config\CliCommand;
use ObjectivePHP\ServicesFactory\ServicesFactory;

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

    /** @var string */
    protected $logger;

    /**
     * LoggerClientPackage constructor.
     * @param string $serviceIdentifier
     */
    public function __construct(
        string $serviceIdentifier = self::DEFAULT_IDENTIFIER,
        string $class = Translate::class,
        string $logger = 'logger.client'
    ) {
        $this->logger = $logger;
        $this->identifier = $serviceIdentifier;
        $this->class = $class;
    }

    public function __invoke(ApplicationInterface $app)
    {
        $params = $app->getConfig()->subset(TranslateParam::class);
        $app->getConfig()->import(new CliCommand(new TranslateCli()));

        // Create translate directory
        if (!is_dir($params->get('translate_directory'))) {
            try {
                mkdir($params->get('translate_directory'), 0755, true);
            } catch (\Exception $e) {
                if ($app->getServicesFactory()->has($this->logger)) {
                    $logger = $app->getServicesFactory()->get($this->logger);
                    $logger->notify($e->getMessage());
                }
            }
        }

        $service = [
            'id' => $this->identifier,
            'alias' => Translate::class,
            'factory' => function (string $id, ServicesFactory $servicesFactory) use ($params) {

                /** @var Translate $translate */
                $translate = new $this->class(
                    [Translate::OPTION_BASEURL => $params->get('base_url')],
                    $params->get('translate_config')
                );

                $transport = $params->get('transport') instanceof SyncTransportInterface
                    ? $params->get('transport')
                    : new BasicTransport();

                $translate->setTransport($transport);

                if ($servicesFactory->has($this->logger)) {
                    $translate->setLogger($servicesFactory->get($this->logger));
                }

                if ($servicesFactory->has($this->connectClientIdentifier)) {
                    /** @var Connect $client */
                    $client = $servicesFactory->get($this->connectClientIdentifier);
                    if ($client->getUser() instanceof User) {
                        $translate->setLang($client->getUser()->getLanguage());
                    }
                } elseif ($params->get('translate_lang')) {
                    $translate->setLang($params->get('translate_lang'));
                }

                // setting the default namespace if it is set
                if ($params->get('translate_namespace')) {
                    $translate->setDomain($params->get('translate_namespace'));
                }

                return $translate;
            },
        ];

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
