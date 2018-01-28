<?php 
namespace Neos\Flow\I18n\Xliff;

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
 * A class which parses XLIFF file to simple but useful array representation.
 *
 * As for now, this class supports only basic XLIFF specification.
 * - it uses only first "file" tag
 * - it does support groups only as defined in [2] in order to support plural
 *   forms
 * - reads only "source" and "target" in "trans-unit" tags
 *
 * @Flow\Scope("singleton")
 * @throws Exception\InvalidXliffDataException
 * @see http://docs.oasis-open.org/xliff/v1.2/os/xliff-core.html [1]
 * @see http://docs.oasis-open.org/xliff/v1.2/xliff-profile-po/xliff-profile-po-1.2-cd02.html#s.detailed_mapping.tu [2]
 * @deprecated use \Neos\Flow\I18n\Xliff\V12\XliffParser instead. Will be removed in Flow 5.0
 * Also consider using
 * @see \Neos\Flow\I18n\Xliff\Service\XliffFileProvider
 * as it is capable of label overrides
 */
class XliffParser_Original extends V12\XliffParser
{
    /**
     * Returns array representation of XLIFF data, starting from a root node.
     *
     * @param \SimpleXMLElement $root A root node
     * @return array An array representing parsed XLIFF
     * @throws Exception\InvalidXliffDataException
     * @deprecated use \Neos\Flow\I18n\Xliff\V12\XliffParser::doParsingFromRoot instead
     * Also, if you don't really care about the parsing but the result, consider using
     * @see \Neos\Flow\I18n\Xliff\Service\XliffFileProvider::getFile()
     * as it is capable of label overrides
     */
    protected function doParsingFromRoot(\SimpleXMLElement $root): array
    {
        return parent::doParsingFromRoot($root)[0];
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\I18n\Xliff;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A class which parses XLIFF file to simple but useful array representation.
 * 
 * As for now, this class supports only basic XLIFF specification.
 * - it uses only first "file" tag
 * - it does support groups only as defined in [2] in order to support plural
 *   forms
 * - reads only "source" and "target" in "trans-unit" tags
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class XliffParser extends XliffParser_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\Flow\I18n\Xliff\XliffParser') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\I18n\Xliff\XliffParser', $this);
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
  'parsedFiles' => 'array',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Flow\I18n\Xliff\XliffParser') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\I18n\Xliff\XliffParser', $this);

        $this->Flow_setRelatedEntities();
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/I18n/Xliff/XliffParser.php
#