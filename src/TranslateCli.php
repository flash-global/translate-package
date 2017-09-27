<?php
namespace Fei\Service\Translate\Package;

use Fei\Service\Translate\Client\Translate;
use Fei\Service\Translate\Entity\I18nString;
use Fei\Service\Translate\Package\Config\TranslateParam;
use Fei\Service\Translate\Package\Exception\TranslateException;
use ObjectivePHP\Application\ApplicationInterface;
use ObjectivePHP\Cli\Action\AbstractCliAction;
use ObjectivePHP\Cli\Action\Parameter\Param;

/**
 * Class TranslateCli
 *
 * @package Fei\Service\Notification\Cli
 */
class TranslateCli extends AbstractCliAction
{
    public function __construct()
    {
        $this->setCommand('translate');
        $this->setDescription('Create/update the translations inside the translate service');
        $this->expects(new Param(
            ['t' => 'translations'],
            'File where the translations are stored'
        ));
        $this->expects(new Param(
            ['l' => 'lang'],
            'The lang of translations'
        ));
    }

    /**
     * @param ApplicationInterface $app
     *
     * @return mixed
     *
     * @throws TranslateException
     */
    public function run(ApplicationInterface $app)
    {
        /** @var Translate $translate */
        $translate = $app->getServicesFactory()->get('translate.client');
        $translations = $this->getParam('translations');
        $lang = $this->getParam('lang');

        if (!is_file($translations)) {
            throw new TranslateException('Translation file `' . $translations . '` not found!', 404);
        }

        if (empty($lang)) {
            throw new TranslateException('Missing parameter lang', 500);
        }

        $translations = require $translations;

        $namespace = $app->getConfig()->subset(TranslateParam::class)->get('translate_namespace');
        $lock = $app->getConfig()->subset(TranslateParam::class)->get('translate_config')['lock_file'] ?? null;

        $updates = [];
        $stores = [];
        
        foreach ($translations as $key => $translation) {
            $i18nString = $translate->find($key, $lang, $namespace);

            // update the existing translation
            if ($i18nString instanceof I18nString) {
                $i18nString->setContent($translation);

                echo 'Updating ' . $key . PHP_EOL;
                $updates[] = $i18nString;
            } else {
                // create the new translation
                $i18nString = new I18nString();
                $i18nString->setContent($translation)
                    ->setKey($key)
                    ->setNamespace($namespace)
                    ->setLang($lang);

                echo 'Creating ' . $key . PHP_EOL;
                $stores[] = $i18nString;
            }
        }

        echo 'Sending request to the API' . PHP_EOL;

        $translate->update($updates);
        $translate->store($stores);

        // removing lock file
        if (is_file($lock)) {
            echo 'Removing the lock file' . PHP_EOL;
            unlink($lock);
        }

        return true;
    }
}
