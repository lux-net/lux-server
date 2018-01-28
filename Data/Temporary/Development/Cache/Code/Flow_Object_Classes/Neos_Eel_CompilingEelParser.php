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
 * A compiling expression parser
 *
 * The matcher functions will generate PHP code according to the expressions.
 * Method calls and object / array access are wrapped using the Context object.
 */
class CompilingEelParser_Original extends EelParser
{
    /**
     * @var integer
     */
    protected $tmpId = 0;

    public function NumberLiteral__finalise(&$self)
    {
        $self['code'] = $self['text'];
    }

    public function StringLiteral_SingleQuotedStringLiteral(&$result, $sub)
    {
        $result['code'] = $sub['text'];
    }

    /**
     * Evaluate a double quoted string literal
     *
     * We need to replace the double quoted string with a
     *
     * @param array $result
     * @param array $sub
     */
    public function StringLiteral_DoubleQuotedStringLiteral(&$result, $sub)
    {
        $result['code'] = '\'' . substr(str_replace(['\'', '\\"'], ['\\\'', '"'], $sub['text']), 1, -1) . '\'';
    }

    public function BooleanLiteral__finalise(&$result)
    {
        $result['code'] = strtoupper($result['text']);
    }

    public function OffsetAccess_Expression(&$result, $sub)
    {
        $result['index'] = $sub['code'];
    }

    public function MethodCall_Identifier(&$result, $sub)
    {
        $result['method'] = '\'' . $sub['text'] . '\'';
    }
    public function MethodCall_Expression(&$result, $sub)
    {
        $result['arguments'][] = $sub['code'];
    }

    public function ObjectPath_Identifier(&$result, $sub)
    {
        $path = $sub['text'];
        if (!array_key_exists('code', $result)) {
            $result['code'] = '$context';
        }
        $result['code'] = $result['code'] . '->getAndWrap(\'' . $path . '\')';
    }

    public function ObjectPath_OffsetAccess(&$result, $sub)
    {
        $path = $sub['index'];
        $result['code'] = $result['code'] . '->getAndWrap(' . $path . ')';
    }

    public function ObjectPath_MethodCall(&$result, $sub)
    {
        $arguments = isset($sub['arguments']) ? $sub['arguments'] : [];
        if (!array_key_exists('code', $result)) {
            $result['code'] = '$context';
        }
        $result['code'] = $result['code'] . '->callAndWrap(' . $sub['method'] . ', array(' . implode(',', $arguments) . '))';
    }

    public function Term_term(&$result, $sub)
    {
        $result['code'] = $sub['code'];
    }

    public function Expression_exp(&$result, $sub)
    {
        $result['code'] = $sub['code'];
    }

    public function SimpleExpression_term(&$result, $sub)
    {
        $result['code'] = $sub['code'];
    }

    public function WrappedExpression_Expression(&$result, $sub)
    {
        $result['code'] = '(' . $sub['code'] . ')';
    }

    public function NotExpression_exp(&$result, $sub)
    {
        $result['code'] = '(!' . $this->unwrapExpression($sub['code']) . ')';
    }

    public function ArrayLiteral_Expression(&$result, $sub)
    {
        if (!isset($result['code'])) {
            $result['code'] = '$context->wrap(array())';
        }
        $result['code'] .= '->push(' . $sub['code'] . ')';
    }

    public function ArrayLiteral__finalise(&$result)
    {
        if (!isset($result['code'])) {
            $result['code'] = '$context->wrap(array())';
        }
    }

    public function ObjectLiteralProperty_Identifier(&$result, $sub)
    {
        $result['code'] = '\'' . $sub['text'] . '\'';
    }

    public function ObjectLiteralProperty_StringLiteral(&$result, $sub)
    {
        $result['code'] = $sub['code'];
    }

    public function ObjectLiteral_ObjectLiteralProperty(&$result, $sub)
    {
        if (!isset($result['code'])) {
            $result['code'] = '$context->wrap(array())';
        }
        $result['code'] .= '->push(' . $sub['value']['code'] . ',' . $sub['key']['code'] . ')';
    }

    public function ObjectLiteral__finalise(&$result)
    {
        if (!isset($result['code'])) {
            $result['code'] = '$context->wrap(array())';
        }
    }

    public function Disjunction_lft(&$result, $sub)
    {
        $result['code'] = $sub['code'];
    }

    public function Disjunction_rgt(&$result, $sub)
    {
        $tmpVarName = '$_' . $this->tmpId++;

        $result['code'] = '((' . $tmpVarName . '=' . $this->unwrapExpression($result['code']) . ')?' . $tmpVarName . ':' . $this->unwrapExpression($sub['code']) . ')';
    }

    public function Conjunction_lft(&$result, $sub)
    {
        $result['code'] = $sub['code'];
    }

    public function Conjunction_rgt(&$result, $sub)
    {
        $tmpVarName = '$_' . $this->tmpId++;

        $result['code'] = '((' . $tmpVarName . '=' . $this->unwrapExpression($result['code']) . ')?(' . $this->unwrapExpression($sub['code']) . '):' . $tmpVarName . ')';
    }

    public function Comparison_lft(&$result, $sub)
    {
        $result['code'] = $sub['code'];
    }

    public function Comparison_comp(&$result, $sub)
    {
        $result['comp'] = $sub['text'];
    }

    /**
     * Return an expression that unwraps the given expression
     * if it is a Context object.
     *
     * @param string $expression
     * @return string
     */
    protected function unwrapExpression($expression)
    {
        $varName = '$_' . $this->tmpId++;
        return '((' . $varName . '=' . $expression . ') instanceof \Neos\Eel\Context?' . $varName . '->unwrap():' . $varName . ')';
    }

    public function Comparison_rgt(&$result, $sub)
    {
        $lval = $this->unwrapExpression($result['code']);
        $rval = $this->unwrapExpression($sub['code']);

        switch ($result['comp']) {
        case '==':
            $result['code'] = '(' . $lval . ')===(' . $rval . ')';
            break;
        case '!=':
            $result['code'] = '(' . $lval . ')!==(' . $rval . ')';
            break;
        case '<':
            $result['code'] = '(' . $lval . ')<(' . $rval . ')';
            break;
        case '<=':
            $result['code'] = '(' . $lval . ')<=(' . $rval . ')';
            break;
        case '>':
            $result['code'] = '(' . $lval . ')>(' . $rval . ')';
            break;
        case '>=':
            $result['code'] = '(' . $lval . ')>=(' . $rval . ')';
            break;
        default:
            throw new ParserException('Unexpected comparison operator "' . $result['comp'] . '"', 1344512571);
        }
    }

    public function SumCalculation_lft(&$result, $sub)
    {
        $result['code'] = $sub['code'];
    }

    public function SumCalculation_op(&$result, $sub)
    {
        $result['op'] = $sub['text'];
    }

    public function SumCalculation_rgt(&$result, $sub)
    {
        $rval = $this->unwrapExpression($sub['code']);
        $lval = $this->unwrapExpression($result['code']);

        switch ($result['op']) {
        case '+':
            $xVarName = '$_x_' . $this->tmpId++;
            $yVarName = '$_y_' . $this->tmpId++;
            $result['code'] = '(is_string(' . $xVarName . '=' . $lval . ')|is_string(' . $yVarName . '=' . $rval . '))?(' . $xVarName . ' . ' . $yVarName . '):(' . $xVarName . '+' . $yVarName . ')';
            break;
        case '-':
            $result['code'] = $lval . '-' . $rval;
            break;
        default:
            throw new ParserException('Unexpected operator "' . $result['op'] . '"', 1344512602);
        }
    }

    public function ProdCalculation_lft(&$result, $sub)
    {
        $result['code'] = $sub['code'];
    }

    public function ProdCalculation_op(&$result, $sub)
    {
        $result['op'] = $sub['text'];
    }

    public function ProdCalculation_rgt(&$result, $sub)
    {
        $rval = $this->unwrapExpression($sub['code']);
        $lval = $this->unwrapExpression($result['code']);

        switch ($result['op']) {
        case '/':
            $result['code'] = $lval . '/' . $rval;
            break;
        case '*':
            $result['code'] = $lval . '*' . $rval;
            break;
        case '%':
            $result['code'] = $lval . '%' . $rval;
            break;
        default:
            throw new ParserException('Unexpected operator "' . $result['op'] . '"', 1344512641);
        }
    }

    public function ConditionalExpression_cond(&$result, $sub)
    {
        $result['code'] = $sub['code'];
    }

    public function ConditionalExpression_then(&$result, $sub)
    {
        $result['code'] = '(' . $this->unwrapExpression($result['code']) . '?(' . $sub['code'] . ')';
    }

    public function ConditionalExpression_else(&$result, $sub)
    {
        $result['code'] .= ':(' . $sub['code'] . '))';
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Eel;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A compiling expression parser
 * 
 * The matcher functions will generate PHP code according to the expressions.
 * Method calls and object / array access are wrapped using the Context object.
 */
class CompilingEelParser extends CompilingEelParser_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        $arguments = func_get_args();
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $string in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
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
  'tmpId' => 'integer',
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
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Eel/Classes/CompilingEelParser.php
#