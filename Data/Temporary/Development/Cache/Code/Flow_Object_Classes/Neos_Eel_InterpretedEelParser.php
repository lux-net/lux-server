<?php 
namespace Neos\Eel;

/*
 * This file is part of the Neos.Eel package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

/**
 * An interpreting expression parser
 *
 * The matcher functions attached to the rules interpret the result
 * given the context in the constructor.
 */
class InterpretedEelParser_Original extends EelParser
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @param string $string
     * @param Context $context The context to interpret
     */
    public function __construct($string, $context)
    {
        parent::__construct($string);
        $this->context = $context;
    }

    public function NumberLiteral__finalise(&$self)
    {
        if (isset($self['dec'])) {
            $self['val'] = (float)($self['text']);
        } else {
            $self['val'] = (integer)$self['text'];
        }
    }

    public function BooleanLiteral__finalise(&$result)
    {
        $result['val'] = strtolower($result['text']) === 'true';
    }

    public function OffsetAccess_Expression(&$result, $sub)
    {
        $result['index'] = $sub['val'];
    }

    public function MethodCall_Identifier(&$result, $sub)
    {
        $result['method'] = $sub['text'];
    }
    public function MethodCall_Expression(&$result, $sub)
    {
        $result['arguments'][] = $sub['val'];
    }

    public function ObjectPath_Identifier(&$result, $sub)
    {
        $path = $sub['text'];
        if (!array_key_exists('val', $result)) {
            $result['val'] = $this->context;
        }
        $result['val'] = $result['val']->getAndWrap($path);
    }

    public function ObjectPath_OffsetAccess(&$result, $sub)
    {
        $path = $sub['index'];
        $result['val'] = $result['val']->getAndWrap($path);
    }

    public function ObjectPath_MethodCall(&$result, $sub)
    {
        $arguments = isset($sub['arguments']) ? $sub['arguments'] : [];
        if (!array_key_exists('val', $result)) {
            $result['val'] = $this->context;
        }
        $result['val'] = $result['val']->callAndWrap($sub['method'], $arguments);
    }

    public function Term_term(&$result, $sub)
    {
        $result['val'] = $sub['val'];
    }

    public function Expression_exp(&$result, $sub)
    {
        $result['val'] = $sub['val'];
    }

    public function SimpleExpression_term(&$result, $sub)
    {
        $result['val'] = $sub['val'];
    }

    public function WrappedExpression_Expression(&$result, $sub)
    {
        $result['val'] = $sub['val'];
    }

    public function NotExpression_exp(&$result, $sub)
    {
        $result['val'] = !(boolean)$sub['val'];
    }

    public function ArrayLiteral_Expression(&$result, $sub)
    {
        if (!isset($result['val'])) {
            $result['val'] = new Context([]);
        }
        $result['val']->push($sub['val']);
    }

    public function ArrayLiteral__finalise(&$result)
    {
        if (!isset($result['val'])) {
            $result['val'] = new Context([]);
        }
    }

    public function ObjectLiteralProperty_Identifier(&$result, $sub)
    {
        $result['val'] = $sub['text'];
    }

    public function ObjectLiteralProperty_StringLiteral(&$result, $sub)
    {
        $result['val'] = $sub['val'];
    }

    public function ObjectLiteral_ObjectLiteralProperty(&$result, $sub)
    {
        if (!isset($result['val'])) {
            $result['val'] = new Context([]);
        }
        $result['val']->push($sub['value']['val'], $sub['key']['val']);
    }

    public function ObjectLiteral__finalise(&$result)
    {
        if (!isset($result['val'])) {
            $result['val'] = new Context([]);
        }
    }

    public function Disjunction_lft(&$result, $sub)
    {
        $result['val'] = $sub['val'];
    }

    public function Disjunction_rgt(&$result, $sub)
    {
        $lft = $this->unwrap($result['val']);
        $rgt = $this->unwrap($sub['val']);
        $result['val'] = $lft ? $lft : $rgt;
    }

    public function Conjunction_lft(&$result, $sub)
    {
        $result['val'] = $sub['val'];
    }

    public function Conjunction_rgt(&$result, $sub)
    {
        $lft = $this->unwrap($result['val']);
        $rgt = $this->unwrap($sub['val']);
        $result['val'] = $lft ? $rgt : $lft;
    }

    public function Comparison_lft(&$result, $sub)
    {
        $result['val'] = $sub['val'];
    }

    public function Comparison_comp(&$result, $sub)
    {
        $result['comp'] = $sub['text'];
    }

    public function Comparison_rgt(&$result, $sub)
    {
        $lval = $this->unwrap($result['val']);
        $rval = $this->unwrap($sub['val']);

        switch ($result['comp']) {
        case '==':
            $result['val'] = $lval === $rval;
            break;
        case '!=':
            $result['val'] = $lval !== $rval;
            break;
        case '<':
            $result['val'] = $lval < $rval;
            break;
        case '<=':
            $result['val'] = $lval <= $rval;
            break;
        case '>':
            $result['val'] = $lval > $rval;
            break;
        case '>=':
            $result['val'] = $lval >= $rval;
            break;
        default:
            throw new ParserException('Unknown comparison operator "' . $result['comp'] . '"', 1344512487);
        }
    }

    public function SumCalculation_lft(&$result, $sub)
    {
        $result['val'] = $sub['val'];
    }

    public function SumCalculation_op(&$result, $sub)
    {
        $result['op'] = $sub['text'];
    }

    public function SumCalculation_rgt(&$result, $sub)
    {
        $lval = $result['val'];
        $rval = $sub['val'];

        switch ($result['op']) {
        case '+':
            if (is_string($lval) || is_string($rval)) {
                // Do not unwrap here and use better __toString handling of Context
                $result['val'] = $lval . $rval;
            } else {
                $result['val'] = $this->unwrap($lval) + $this->unwrap($rval);
            }
            break;
        case '-':
            $result['val'] = $this->unwrap($lval) - $this->unwrap($rval);
            break;
        }
    }

    public function ProdCalculation_lft(&$result, $sub)
    {
        $result['val'] = $sub['val'];
    }

    public function ProdCalculation_op(&$result, $sub)
    {
        $result['op'] = $sub['text'];
    }

    public function ProdCalculation_rgt(&$result, $sub)
    {
        $lval = $this->unwrap($result['val']);
        $rval = $this->unwrap($sub['val']);

        switch ($result['op']) {
        case '/':
            $result['val'] = $lval / $rval;
            break;
        case '*':
            $result['val'] = $lval * $rval;
            break;
        case '%':
            $result['val'] = $lval % $rval;
            break;
        }
    }

    public function ConditionalExpression_cond(&$result, $sub)
    {
        $result['val'] = $sub['val'];
    }

    public function ConditionalExpression_then(&$result, $sub)
    {
        $result['then'] = $sub['val'];
    }

    public function ConditionalExpression_else(&$result, $sub)
    {
        if ((boolean)$this->unwrap($result['val'])) {
            $result['val'] = $result['then'];
        } else {
            $result['val'] = $sub['val'];
        }
    }

    /**
     * If $value is an instance of Context, the result of unwrap()
     * is returned, otherwise $value is returned unchanged.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function unwrap($value)
    {
        if ($value instanceof Context) {
            return $value->unwrap();
        } else {
            return $value;
        }
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Eel;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * An interpreting expression parser
 * 
 * The matcher functions attached to the rules interpret the result
 * given the context in the constructor.
 */
class InterpretedEelParser extends InterpretedEelParser_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait;


    /**
     * Autogenerated Proxy Method
     * @param string $string
     * @param Context $context The context to interpret
     */
    public function __construct()
    {
        $arguments = func_get_args();
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $string in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(1, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $context in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        call_user_func_array('parent::__construct', $arguments);
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
  'context' => 'Neos\\Eel\\Context',
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
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Eel/Classes/InterpretedEelParser.php
#