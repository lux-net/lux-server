<?php 
namespace Neos\Flow\I18n;

/*
 * This file is part of the Neos.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\I18n;

/**
 * A class for translating messages
 *
 * Messages (labels) can be translated in two modes:
 * - by original label: untranslated label is used as a key
 * - by ID: string identifier is used as a key (eg. user.noaccess)
 *
 * Correct plural form of translated message is returned when $quantity
 * parameter is provided to a method. Otherwise, or on failure just translated
 * version is returned (eg. when string is translated only to one form).
 *
 * When all fails, untranslated (original) string or ID is returned (depends on
 * translation method).
 *
 * Placeholders' resolving is done when needed (see FormatResolver class).
 *
 * Actual translating is done by injected TranslationProvider instance, so
 * storage format depends on concrete implementation.
 *
 * @Flow\Scope("singleton")
 * @api
 * @see FormatResolver
 * @see TranslationProvider\TranslationProviderInterface
 * @see Cldr\Reader\PluralsReader
 */
class Translator_Original
{
    /**
     * @var I18n\Service
     */
    protected $localizationService;

    /**
     * @var TranslationProvider\TranslationProviderInterface
     */
    protected $translationProvider;

    /**
     * @var FormatResolver
     */
    protected $formatResolver;

    /**
     * @var Cldr\Reader\PluralsReader
     */
    protected $pluralsReader;

    /**
     * @param I18n\Service $localizationService
     * @return void
     */
    public function injectLocalizationService(I18n\Service $localizationService)
    {
        $this->localizationService = $localizationService;
    }

    /**
     * @param TranslationProvider\TranslationProviderInterface $translationProvider
     * @return void
     */
    public function injectTranslationProvider(TranslationProvider\TranslationProviderInterface $translationProvider)
    {
        $this->translationProvider = $translationProvider;
    }

    /**
     * @param FormatResolver $formatResolver
     * @return void
     */
    public function injectFormatResolver(FormatResolver $formatResolver)
    {
        $this->formatResolver = $formatResolver;
    }

    /**
     * @param Cldr\Reader\PluralsReader $pluralsReader
     * @return void
     */
    public function injectPluralsReader(Cldr\Reader\PluralsReader $pluralsReader)
    {
        $this->pluralsReader = $pluralsReader;
    }

    /**
     * Translates the message given as $originalLabel.
     *
     * Searches for a translation in the source as defined by $sourceName
     * (interpretation depends on concrete translation provider used).
     *
     * If any arguments are provided in the $arguments array, they will be inserted
     * to the translated string (in place of corresponding placeholders, with
     * format defined by these placeholders).
     *
     * If $quantity is provided, correct plural form for provided $locale will
     * be chosen and used to choose correct translation variant.
     *
     * If no $locale is provided, default system locale will be used.
     *
     * @param string $originalLabel Untranslated message
     * @param array $arguments An array of values to replace placeholders with
     * @param mixed $quantity A number to find plural form for (float or int), NULL to not use plural forms
     * @param Locale $locale Locale to use (NULL for default one)
     * @param string $sourceName Name of file with translations, base path is $packageKey/Resources/Private/Locale/Translations/
     * @param string $packageKey Key of the package containing the source file
     * @return string Translated $originalLabel or $originalLabel itself on failure
     * @api
     */
    public function translateByOriginalLabel($originalLabel, array $arguments = [], $quantity = null, Locale $locale = null, $sourceName = 'Main', $packageKey = 'Neos.Flow')
    {
        if ($locale === null) {
            $locale = $this->localizationService->getConfiguration()->getCurrentLocale();
        }
        $pluralForm = $this->getPluralForm($quantity, $locale);

        $translatedMessage = $this->translationProvider->getTranslationByOriginalLabel($originalLabel, $locale, $pluralForm, $sourceName, $packageKey);

        if ($translatedMessage === false) {
            $translatedMessage = $originalLabel;
        }

        if (!empty($arguments)) {
            $translatedMessage = $this->formatResolver->resolvePlaceholders($translatedMessage, $arguments, $locale);
        }

        return $translatedMessage;
    }

    /**
     * Returns translated string found under the $labelId.
     *
     * Searches for a translation in the source as defined by $sourceName
     * (interpretation depends on concrete translation provider used).
     *
     * If any arguments are provided in the $arguments array, they will be inserted
     * to the translated string (in place of corresponding placeholders, with
     * format defined by these placeholders).
     *
     * If $quantity is provided, correct plural form for provided $locale will
     * be chosen and used to choose correct translation variant.
     *
     * @param string $labelId Key to use for finding translation
     * @param array $arguments An array of values to replace placeholders with
     * @param mixed $quantity A number to find plural form for (float or int), NULL to not use plural forms
     * @param Locale $locale Locale to use (NULL for default one)
     * @param string $sourceName Name of file with translations, base path is $packageKey/Resources/Private/Locale/Translations/
     * @param string $packageKey Key of the package containing the source file
     * @return string Translated message or NULL on failure
     * @api
     * @see Translator::translateByOriginalLabel()
     */
    public function translateById($labelId, array $arguments = [], $quantity = null, Locale $locale = null, $sourceName = 'Main', $packageKey = 'Neos.Flow')
    {
        if ($locale === null) {
            $locale = $this->localizationService->getConfiguration()->getCurrentLocale();
        }
        $pluralForm = $this->getPluralForm($quantity, $locale);

        $translatedMessage = $this->translationProvider->getTranslationById($labelId, $locale, $pluralForm, $sourceName, $packageKey);
        if ($translatedMessage === false) {
            return null;
        }

        if (!empty($arguments)) {
            return $this->formatResolver->resolvePlaceholders($translatedMessage, $arguments, $locale);
        }
        return $translatedMessage;
    }

    /**
     * Get the plural form to be used.
     *
     * If $quantity is numeric and non-NULL, the plural form for provided $locale will be
     * chosen according to it.
     *
     * In all other cases, NULL is returned.
     *
     * @param mixed $quantity
     * @param Locale $locale
     * @return string
     */
    protected function getPluralForm($quantity, Locale $locale)
    {
        if (!is_numeric($quantity)) {
            return null;
        } else {
            return $this->pluralsReader->getPluralForm($quantity, $locale);
        }
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\I18n;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A class for translating messages
 * 
 * Messages (labels) can be translated in two modes:
 * - by original label: untranslated label is used as a key
 * - by ID: string identifier is used as a key (eg. user.noaccess)
 * 
 * Correct plural form of translated message is returned when $quantity
 * parameter is provided to a method. Otherwise, or on failure just translated
 * version is returned (eg. when string is translated only to one form).
 * 
 * When all fails, untranslated (original) string or ID is returned (depends on
 * translation method).
 * 
 * Placeholders' resolving is done when needed (see FormatResolver class).
 * 
 * Actual translating is done by injected TranslationProvider instance, so
 * storage format depends on concrete implementation.
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class Translator extends Translator_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\Flow\I18n\Translator') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\I18n\Translator', $this);
        if ('Neos\Flow\I18n\Translator' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __sleep()
    {
            $result = NULL;
        $this->Flow_Object_PropertiesToSerialize = array();

        $transientProperties = array (
);
        $propertyVarTags = array (
  'localizationService' => 'Neos\\Flow\\I18n\\Service',
  'translationProvider' => 'Neos\\Flow\\I18n\\TranslationProvider\\TranslationProviderInterface',
  'formatResolver' => 'Neos\\Flow\\I18n\\FormatResolver',
  'pluralsReader' => 'Neos\\Flow\\I18n\\Cldr\\Reader\\PluralsReader',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Flow\I18n\Translator') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\I18n\Translator', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->injectLocalizationService(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\I18n\Service'));
        $this->injectTranslationProvider(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\I18n\TranslationProvider\TranslationProviderInterface'));
        $this->injectFormatResolver(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\I18n\FormatResolver'));
        $this->injectPluralsReader(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\I18n\Cldr\Reader\PluralsReader'));
        $this->Flow_Injected_Properties = array (
  0 => 'localizationService',
  1 => 'translationProvider',
  2 => 'formatResolver',
  3 => 'pluralsReader',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/I18n/Translator.php
#