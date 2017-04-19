# Translate Package

This package provide Translate Client integration for Objective PHP applications.

## Installation

Translate Package needs **PHP 7.0** or up to run correctly.

You will have to integrate it to your Objective PHP project with `composer require fei/translate-package`.


## Integration

As shown below, the Translate Package must be plugged in the application initialization method.

The Translate Package create a Translate Client service that will be consumed by the application's middlewares.

```php
<?php

use ObjectivePHP\Application\AbstractApplication;
use Fei\Service\Translate\Package\TranslatePackage;

class Application extends AbstractApplication
{
    public function init()
    {
        // Define some application steps
        $this->addSteps('bootstrap', 'init', 'auth', 'route', 'rendering');

        // Initializations...

        // Plugging the Translate Package in the bootstrap step
        $this->getStep('bootstrap')
        ->plug(TranslatePackage::class);

        // Another initializations...
    }
}
```
### Application configuration

Create a file in your configuration directory and put your Translate configuration as below:

```php
<?php

use Fei\Service\Translate\Package\Config\TranslateParam;
use Fei\ApiClient\Transport\BasicTransport;

return [
    new TranslateParam('base_url', 'http://translate.api/'), // Translate API URL and port
    new TranslateParam('transport', new BasicTransport()), // Transport type
    new TranslateParam('translate_directory', '/app/translate/'), // Directory to store the translations
    new TranslateParam('translate_config', [
        'lock_file'         => '/app/translate/.translations.lock',
        'data_path'         => '/app/translate/data',
        'translations_path' => '/app/translate/translations',
        'servers'           => [
            'http://translate.api/' => [
                'namespaces' => ['/mynamespace']
            ]
        ],
        'url' => 'http://translate.domain.dev/handleRequest.php'
    ]),// Translate client config (Cf. Translate Client documentation)
    new TranslateParam('translate_namespace', '/mynamespace') // Namespace defined in translate_config where to search the translations
    new TranslateParam('translate_lang', 'en_GB') // Language defined in which we want the translations
];
```

In the previous example you need to set this configuration:

* `base_url` : represent the URL where the API can be contacted in order to send the translations
* `transport` : represent the translations transport type
* `translate_directory` : represent the path to the directory to store the translations
* `translate_config` : represent the translate client configuration (Cf. `translate-client` documentation)
* `translate_namespace` : represent the default namespace where to search the translations
* `translate_lang` : represent the default language in which we want the translations

Please check out `translate-client` documentation for more information about how to use this client.
