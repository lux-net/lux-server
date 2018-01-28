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

/**
 * The LocaleCollection class contains all locales available in current
 * Flow installation, and describes hierarchical relations between them.
 *
 * This class maintans a hierarchical relation between locales. For
 * example, a locale "en_GB" will be a child of a locale "en".
 *
 * @Flow\Scope("singleton")
 */
class LocaleCollection_Original
{
    /**
     * This array contains all locales added to this collection.
     *
     * The values are Locale objects, and the keys are these locale's tags.
     *
     * @var array<Locale>
     */
    protected $localeCollection = [];

    /**
     * This array contains a parent Locale objects for given locale.
     *
     * "Searching" is done by the keys, which are locale tags. The key points to
     * the value which is a parent Locale object. If it's not set, there is no
     * parent for given locale, or no parent was searched before.
     *
     * @var array<Locale>
     */
    protected $localeParentCollection = [];

    /**
     * Adds a locale to the collection.
     *
     * @param Locale $locale The Locale to be inserted
     * @return boolean FALSE when same locale was already inserted before
     */
    public function addLocale(Locale $locale)
    {
        if (isset($this->localeCollection[(string)$locale])) {
            return false;
        }

        // We need to invalidate the parent's array as it could be inaccurate
        $this->localeParentCollection = [];

        $this->localeCollection[(string)$locale] = $locale;
        return true;
    }

    /**
     * Returns a parent Locale object of the locale provided.
     *
     * The parent is a locale which is more generic than the one given as
     * parameter. For example, the parent for locale en_GB will be locale en, of
     * course if it exists in the locale tree of available locales.
     *
     * This method returns NULL when no parent locale is available, or when
     * Locale object provided is not in the tree (ie it's not in a group of
     * available locales).
     *
     * Note: to find a best-matching locale to one which doesn't exist in the
     * system, please use findBestMatchingLocale() method of this class.
     *
     * @param Locale $locale The Locale to search parent for
     * @return mixed Existing Locale instance or NULL on failure
     */
    public function getParentLocaleOf(Locale $locale)
    {
        $localeIdentifier = (string)$locale;

        if (!isset($this->localeCollection[$localeIdentifier])) {
            return null;
        }

        if (isset($this->localeParentCollection[$localeIdentifier])) {
            return $this->localeParentCollection[$localeIdentifier];
        }

        $parentLocaleIdentifier = $localeIdentifier;
        do {
            // Remove the last (most specific) part of the locale tag
            $parentLocaleIdentifier = substr($parentLocaleIdentifier, 0, (int)strrpos($parentLocaleIdentifier, '_'));

            if (isset($this->localeCollection[$parentLocaleIdentifier])) {
                return $this->localeParentCollection[$localeIdentifier] = $this->localeCollection[$parentLocaleIdentifier];
            }
        } while (strrpos($parentLocaleIdentifier, '_') !== false);

        return null;
    }

    /**
     * Returns Locale object which represents one of locales installed and which
     * is most similar to the "template" Locale object given as parameter.
     *
     * @param Locale $locale The "template" locale to be matched
     * @return mixed Existing Locale instance on success, NULL on failure
     */
    public function findBestMatchingLocale(Locale $locale)
    {
        $localeIdentifier = (string)$locale;

        if (isset($this->localeCollection[$localeIdentifier])) {
            return $this->localeCollection[$localeIdentifier];
        }

        $parentLocaleIdentifier = $localeIdentifier;
        do {
            // Remove the last (most specific) part of the locale tag
            $parentLocaleIdentifier = substr($parentLocaleIdentifier, 0, (int)strrpos($parentLocaleIdentifier, '_'));

            if (isset($this->localeCollection[$parentLocaleIdentifier])) {
                return $this->localeCollection[$parentLocaleIdentifier];
            }
        } while (strrpos($parentLocaleIdentifier, '_') !== false);

        return null;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\I18n;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * The LocaleCollection class contains all locales available in current
 * Flow installation, and describes hierarchical relations between them.
 * 
 * This class maintans a hierarchical relation between locales. For
 * example, a locale "en_GB" will be a child of a locale "en".
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class LocaleCollection extends LocaleCollection_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\Flow\I18n\LocaleCollection') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\I18n\LocaleCollection', $this);
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
  'localeCollection' => 'array<Neos\\Flow\\I18n\\Locale>',
  'localeParentCollection' => 'array<Neos\\Flow\\I18n\\Locale>',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Flow\I18n\LocaleCollection') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\I18n\LocaleCollection', $this);

        $this->Flow_setRelatedEntities();
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/I18n/LocaleCollection.php
#