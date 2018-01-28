<?php 
namespace Neos\FluidAdaptor\Service;

/*
 * This file is part of the Neos.FluidAdaptor package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Reflection\ClassReflection;
use Neos\FluidAdaptor\Core\ViewHelper\ArgumentDefinition;

/**
 * XML Schema (XSD) Generator. Will generate an XML schema which can be used for auto-completion
 * in schema-aware editors like Eclipse XML editor.
 */
class XsdGenerator_Original extends AbstractGenerator
{
    /**
     * @var \Neos\Flow\ObjectManagement\ObjectManagerInterface
     * @Flow\Inject
     */
    protected $objectManager;

    /**
     * Generate the XML Schema definition for a given namespace.
     * It will generate an XSD file for all view helpers in this namespace.
     *
     * @param string $viewHelperNamespace Namespace identifier to generate the XSD for, without leading Backslash.
     * @param string $xsdNamespace $xsdNamespace unique target namespace used in the XSD schema (for example "http://yourdomain.org/ns/viewhelpers")
     * @return string XML Schema definition
     * @throws Exception
     */
    public function generateXsd($viewHelperNamespace, $xsdNamespace)
    {
        if (substr($viewHelperNamespace, -1) !== '\\') {
            $viewHelperNamespace .= '\\';
        }

        $classNames = $this->getClassNamesInNamespace($viewHelperNamespace);
        if (count($classNames) === 0) {
            throw new Exception(sprintf('No ViewHelpers found in namespace "%s"', $viewHelperNamespace), 1330029328);
        }

        $xmlRootNode = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
			<xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema" targetNamespace="' . $xsdNamespace . '"></xsd:schema>');

        foreach ($classNames as $className) {
            $this->generateXmlForClassName($className, $viewHelperNamespace, $xmlRootNode);
        }

        return $xmlRootNode->asXML();
    }

    /**
     * Generate the XML Schema for a given class name.
     *
     * @param string $className Class name to generate the schema for.
     * @param string $viewHelperNamespace Namespace prefix. Used to split off the first parts of the class name.
     * @param \SimpleXMLElement $xmlRootNode XML root node where the xsd:element is appended.
     * @return void
     */
    protected function generateXmlForClassName($className, $viewHelperNamespace, \SimpleXMLElement $xmlRootNode)
    {
        $reflectionClass = new ClassReflection($className);
        if (!$reflectionClass->isSubclassOf($this->abstractViewHelperReflectionClass)) {
            return;
        }

        $tagName = $this->getTagNameForClass($className, $viewHelperNamespace);

        $xsdElement = $xmlRootNode->addChild('xsd:element');
        $xsdElement['name'] = $tagName;
        $this->docCommentParser->parseDocComment($reflectionClass->getDocComment());
        $this->addDocumentation($this->docCommentParser->getDescription(), $xsdElement);

        $xsdComplexType = $xsdElement->addChild('xsd:complexType');
        $xsdComplexType['mixed'] = 'true';
        $xsdSequence = $xsdComplexType->addChild('xsd:sequence');
        $xsdAny = $xsdSequence->addChild('xsd:any');
        $xsdAny['minOccurs'] = '0';
        $xsdAny['maxOccurs'] = 'unbounded';

        $this->addAttributes($className, $xsdComplexType);
    }

    /**
     * Add attribute descriptions to a given tag.
     * Initializes the view helper and its arguments, and then reads out the list of arguments.
     *
     * @param string $className Class name where to add the attribute descriptions
     * @param \SimpleXMLElement $xsdElement XML element to add the attributes to.
     * @return void
     */
    protected function addAttributes($className, \SimpleXMLElement $xsdElement)
    {
        $viewHelper = $this->objectManager->get($className);
        $argumentDefinitions = $viewHelper->prepareArguments();

        /** @var $argumentDefinition ArgumentDefinition */
        foreach ($argumentDefinitions as $argumentDefinition) {
            $xsdAttribute = $xsdElement->addChild('xsd:attribute');
            $xsdAttribute['type'] = 'xsd:string';
            $xsdAttribute['name'] = $argumentDefinition->getName();
            $this->addDocumentation($argumentDefinition->getDescription(), $xsdAttribute);
            if ($argumentDefinition->isRequired()) {
                $xsdAttribute['use'] = 'required';
            }
        }
    }

    /**
     * Add documentation XSD to a given XML node
     *
     * @param string $documentation Documentation string to add.
     * @param \SimpleXMLElement $xsdParentNode Node to add the documentation to
     * @return void
     */
    protected function addDocumentation($documentation, \SimpleXMLElement $xsdParentNode)
    {
        $xsdAnnotation = $xsdParentNode->addChild('xsd:annotation');
        $this->addChildWithCData($xsdAnnotation, 'xsd:documentation', $documentation);
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\FluidAdaptor\Service;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * XML Schema (XSD) Generator. Will generate an XML schema which can be used for auto-completion
 * in schema-aware editors like Eclipse XML editor.
 */
class XsdGenerator extends XsdGenerator_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        parent::__construct();
        if ('Neos\FluidAdaptor\Service\XsdGenerator' === get_class($this)) {
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
  'objectManager' => '\\Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
  'abstractViewHelperReflectionClass' => 'Neos\\Flow\\Reflection\\ClassReflection',
  'docCommentParser' => '\\Neos\\Flow\\Reflection\\DocCommentParser',
  'reflectionService' => '\\Neos\\Flow\\Reflection\\ReflectionService',
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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ObjectManagement\ObjectManagerInterface', 'Neos\Flow\ObjectManagement\ObjectManager', 'objectManager', '9524ff5e5332c1890aa361e5d186b7b6', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Reflection\DocCommentParser', 'Neos\Flow\Reflection\DocCommentParser', 'docCommentParser', '24599936009cafd71043e063c60d1845', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Reflection\DocCommentParser'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Reflection\ReflectionService', 'Neos\Flow\Reflection\ReflectionService', 'reflectionService', '464c26aa94c66579c050985566cbfc1f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Reflection\ReflectionService'); });
        $this->Flow_Injected_Properties = array (
  0 => 'objectManager',
  1 => 'docCommentParser',
  2 => 'reflectionService',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.FluidAdaptor/Classes/Service/XsdGenerator.php
#