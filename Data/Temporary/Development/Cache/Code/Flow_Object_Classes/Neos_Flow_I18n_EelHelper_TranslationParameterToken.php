<?php 
namespace Neos\Flow\I18n\EelHelper;

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
use Neos\Flow\I18n\Exception\InvalidLocaleIdentifierException;
use Neos\Flow\I18n\Locale;
use Neos\Flow\I18n\Translator;
use Neos\Flow\Exception as FlowException;
use Neos\Eel\ProtectedContextAwareInterface;

/**
 * Provides a chainable interface to collect all arguments needed to
 * translate messages using source message or key ID
 *
 * It also translates labels according to the configuration it stores
 */
class TranslationParameterToken_Original implements ProtectedContextAwareInterface
{
    /**
     * @var Translator
     */
    protected $translator;

    /**
     * Key/Value store to keep the collected parameters
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * @param string $id
     * @param string $value
     */
    public function __construct($id = null, $value = null)
    {
        if ($id !== null) {
            $this->parameters['id'] = $id;
        }

        if ($value !== null) {
            $this->parameters['value'] = $value;
        }
    }

    /**
     * Inject the translator into the token.
     * Used for testing.
     *
     * @param Translator $translator
     */
    public function injectTranslator(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Set the id.
     *
     * @param string $id Id to use for finding translation (trans-unit id in XLIFF)
     * @return TranslationParameterToken
     */
    public function id($id)
    {
        $this->parameters['id'] = $id;
        return $this;
    }

    /**
     * Set the original translation value (the untranslated source string).
     *
     * @param string $value
     * @return TranslationParameterToken
     */
    public function value($value)
    {
        $this->parameters['value'] = $value;
        return $this;
    }

    /**
     * Set the arguments.
     *
     * @param array $arguments Numerically indexed array of values to be inserted into placeholders
     * @return TranslationParameterToken
     */
    public function arguments(array $arguments)
    {
        $this->parameters['arguments'] = $arguments;
        return $this;
    }

    /**
     * Set the source.
     *
     * @param string $source Name of file with translations
     * @return TranslationParameterToken
     */
    public function source($source)
    {
        $this->parameters['source'] = $source;
        return $this;
    }

    /**
     * Set the package.
     *
     * @param string $package Target package key. If not set, the current package key will be used
     * @return TranslationParameterToken
     */
    public function package($package)
    {
        $this->parameters['package'] = $package;
        return $this;
    }

    /**
     * Set the quantity.
     *
     * @param mixed $quantity A number to find plural form for (float or int), NULL to not use plural forms
     * @return TranslationParameterToken
     */
    public function quantity($quantity)
    {
        $this->parameters['quantity'] = $quantity;
        return $this;
    }

    /**
     * Set the locale.
     * The locale Identifier will be converted into a Locale
     *
     * @param string $locale An identifier of locale to use (NULL for use the default locale)
     * @return TranslationParameterToken
     * @throws FlowException
     */
    public function locale($locale)
    {
        try {
            $this->parameters['locale'] = new Locale($locale);
        } catch (InvalidLocaleIdentifierException $e) {
            throw new FlowException(sprintf('"%s" is not a valid locale identifier.', $locale), 1436784806);
        }

        return $this;
    }

    /**
     * Translate according to currently collected parameters
     *
     * @param array $overrides An associative array to override the collected parameters
     * @return string
     */
    public function translate(array $overrides = [])
    {
        array_replace_recursive($this->parameters, $overrides);

        $id = isset($this->parameters['id']) ? $this->parameters['id'] : null;
        $value = isset($this->parameters['value']) ? $this->parameters['value'] : null;
        $arguments = isset($this->parameters['arguments']) ? $this->parameters['arguments'] : [];
        $source = isset($this->parameters['source']) ? $this->parameters['source'] : 'Main';
        $package = isset($this->parameters['package']) ? $this->parameters['package'] : null;
        $quantity = isset($this->parameters['quantity']) ? $this->parameters['quantity'] : null;
        $locale = isset($this->parameters['locale']) ? $this->parameters['locale'] : null;

        if ($id === null) {
            return $this->translator->translateByOriginalLabel($value, $arguments, $quantity, $locale, $source, $package);
        }

        $translation = $this->translator->translateById($id, $arguments, $quantity, $locale, $source, $package);
        if ($translation === null && $value !== null) {
            return $this->translator->translateByOriginalLabel($value, $arguments, $quantity, $locale, $source, $package);
        }

        return $translation;
    }

    /**
     * Runs translate to avoid the need of calling translate as a finishing method
     */
    public function __toString()
    {
        return $this->translate();
    }

    /**
     * All methods are considered safe
     *
     * @param string $methodName
     * @return boolean
     */
    public function allowsCallOfMethod($methodName)
    {
        return true;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\I18n\EelHelper;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Provides a chainable interface to collect all arguments needed to
 * translate messages using source message or key ID
 * 
 * It also translates labels according to the configuration it stores
 */
class TranslationParameterToken extends TranslationParameterToken_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     * @param string $id
     * @param string $value
     */
    public function __construct()
    {
        $arguments = func_get_args();
        call_user_func_array('parent::__construct', $arguments);
        if ('Neos\Flow\I18n\EelHelper\TranslationParameterToken' === get_class($this)) {
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
  'translator' => 'Neos\\Flow\\I18n\\Translator',
  'parameters' => 'array',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->injectTranslator(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\I18n\Translator'));
        $this->Flow_Injected_Properties = array (
  0 => 'translator',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/I18n/EelHelper/TranslationParameterToken.php
#