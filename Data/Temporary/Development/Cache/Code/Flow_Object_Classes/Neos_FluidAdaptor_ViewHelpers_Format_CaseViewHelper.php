<?php 
namespace Neos\FluidAdaptor\ViewHelpers\Format;

/*
 * This file is part of the Neos.FluidAdaptor package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\FluidAdaptor\Core\ViewHelper\AbstractViewHelper;
use Neos\FluidAdaptor\Core\ViewHelper\Exception\InvalidVariableException;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Modifies the case of an input string to upper- or lowercase or capitalization.
 * The default transformation will be uppercase as in ``mb_convert_case`` [1].
 *
 * Possible modes are:
 *
 * ``lower``
 *   Transforms the input string to its lowercase representation
 *
 * ``upper``
 *   Transforms the input string to its uppercase representation
 *
 * ``capital``
 *   Transforms the input string to its first letter upper-cased, i.e. capitalization
 *
 * ``uncapital``
 *   Transforms the input string to its first letter lower-cased, i.e. uncapitalization
 *
 * ``capitalWords``
 *   Transforms the input string to each containing word being capitalized
 *
 * Note that the behavior will be the same as in the appropriate PHP function ``mb_convert_case`` [1];
 * especially regarding locale and multibyte behavior.
 *
 * @see http://php.net/manual/function.mb-convert-case.php [1]
 *
 * = Examples =
 *
 * <code title="Example">
 * <f:format.case>Some Text with miXed case</f:format.case>
 * </code>
 * <output>
 * SOME TEXT WITH MIXED CASE
 * </output>
 *
 * <code title="Example with given mode">
 * <f:format.case mode="capital">someString</f:format.case>
 * </code>
 * <output>
 * SomeString
 * </output>
 *
 * <code title="Inline notation">
 * {article.title -> f:format.case(mode: 'capitalWords')}
 * </code>
 * <output>
 * Dolphins Vanish After A Surprisingly Sophisticated Attempt To Do A Double Backward Somersault
 * </output>
 *
 * @api
 */
class CaseViewHelper_Original extends AbstractViewHelper
{
    /**
     * @var boolean
     */
    protected $escapeChildren = false;

    /**
     * Directs the input string being converted to "lowercase"
     */
    const CASE_LOWER = 'lower';

    /**
     * Directs the input string being converted to "UPPERCASE"
     */
    const CASE_UPPER = 'upper';

    /**
     * Directs the input string being converted to "Capital case"
     */
    const CASE_CAPITAL = 'capital';

    /**
     * Directs the input string being converted to "unCapital case"
     */
    const CASE_UNCAPITAL = 'uncapital';

    /**
     * Directs the input string being converted to "Capital Case For Each Word"
     */
    const CASE_CAPITAL_WORDS = 'capitalWords';

    /**
     * Changes the case of the input string
     *
     * @param string $value The input value. If not given, the evaluated child nodes will be used
     * @param string $mode The case to apply, must be one of this' CASE_* constants. Defaults to uppercase application
     * @return string the altered string.
     * @throws InvalidVariableException
     * @api
     */
    public function render($value = null, $mode = self::CASE_UPPER)
    {
        return self::renderStatic(['value' => $value, 'mode' => $mode], $this->buildRenderChildrenClosure(), $this->renderingContext);
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     * @throws InvalidVariableException
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $value = $arguments['value'];
        $mode = $arguments['mode'];
        if ($value === null) {
            $value = $renderChildrenClosure();
        }

        $originalEncoding = mb_internal_encoding();
        mb_internal_encoding('UTF-8');

        switch ($mode) {
            case self::CASE_LOWER:
                $output = mb_convert_case($value, \MB_CASE_LOWER);
                break;
            case self::CASE_UPPER:
                $output = mb_convert_case($value, \MB_CASE_UPPER);
                break;
            case self::CASE_CAPITAL:
                $output = mb_substr(mb_convert_case($value, \MB_CASE_UPPER), 0, 1) . mb_substr($value, 1);
                break;
            case self::CASE_UNCAPITAL:
                $output = mb_substr(mb_convert_case($value, \MB_CASE_LOWER), 0, 1) . mb_substr($value, 1);
                break;
            case self::CASE_CAPITAL_WORDS:
                $output = mb_convert_case($value, \MB_CASE_TITLE);
                break;
            default:
                mb_internal_encoding($originalEncoding);
                throw new InvalidVariableException('The case mode "' . $mode . '" supplied to Fluid\'s format.case ViewHelper is not supported.', 1358349150);
        }

        mb_internal_encoding($originalEncoding);

        return $output;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\FluidAdaptor\ViewHelpers\Format;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Modifies the case of an input string to upper- or lowercase or capitalization.
 * The default transformation will be uppercase as in ``mb_convert_case`` [1].
 * 
 * Possible modes are:
 * 
 * ``lower``
 *   Transforms the input string to its lowercase representation
 * 
 * ``upper``
 *   Transforms the input string to its uppercase representation
 * 
 * ``capital``
 *   Transforms the input string to its first letter upper-cased, i.e. capitalization
 * 
 * ``uncapital``
 *   Transforms the input string to its first letter lower-cased, i.e. uncapitalization
 * 
 * ``capitalWords``
 *   Transforms the input string to each containing word being capitalized
 * 
 * Note that the behavior will be the same as in the appropriate PHP function ``mb_convert_case`` [1];
 * especially regarding locale and multibyte behavior.
 */
class CaseViewHelper extends CaseViewHelper_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if ('Neos\FluidAdaptor\ViewHelpers\Format\CaseViewHelper' === get_class($this)) {
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
  'escapeChildren' => 'boolean',
  'controllerContext' => 'Neos\\Flow\\Mvc\\Controller\\ControllerContext',
  'objectManager' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
  'systemLogger' => 'Neos\\Flow\\Log\\SystemLoggerInterface',
  'argumentDefinitions' => 'ArgumentDefinition[]',
  'viewHelperNode' => 'TYPO3Fluid\\Fluid\\Core\\Parser\\SyntaxTree\\ViewHelperNode',
  'arguments' => 'array',
  'childNodes' => 'NodeInterface[] array',
  'templateVariableContainer' => 'TYPO3Fluid\\Fluid\\Core\\Variables\\VariableProviderInterface',
  'renderingContext' => 'TYPO3Fluid\\Fluid\\Core\\Rendering\\RenderingContextInterface',
  'renderChildrenClosure' => '\\Closure',
  'viewHelperVariableContainer' => 'TYPO3Fluid\\Fluid\\Core\\ViewHelper\\ViewHelperVariableContainer',
  'escapeOutput' => 'boolean',
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
        $this->injectObjectManager(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'));
        $this->injectSystemLogger(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\SystemLoggerInterface'));
        $this->Flow_Injected_Properties = array (
  0 => 'objectManager',
  1 => 'systemLogger',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.FluidAdaptor/Classes/ViewHelpers/Format/CaseViewHelper.php
#