<?php 
namespace Neos\Flow\Persistence\Generic\Qom;

/*
 * This file is part of the Neos.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */


/**
 * Filters tuples based on the outcome of a binary operation.
 *
 * For any comparison, operand2 always evaluates to a scalar value. In contrast,
 * operand1 may evaluate to an array of values (for example, the value of a multi-valued
 * property), in which case the comparison is separately performed for each element
 * of the array, and the Comparison constraint is satisfied as a whole if the
 * comparison against any element of the array is satisfied.
 *
 * If operand1 and operand2 evaluate to values of different property types, the
 * value of operand2 is converted to the property type of the value of operand1.
 * If the type conversion fails, the query is invalid.
 *
 * If operator is not supported for the property type of operand1, the query is invalid.
 *
 * If operand1 evaluates to null (for example, if the operand evaluates the value
 * of a property which does not exist), the constraint is not satisfied.
 *
 * The OPERATOR_EQUAL_TO operator is satisfied only if the value of operand1
 * equals the value of operand2.
 *
 * The OPERATOR_NOT_EQUAL_TO operator is satisfied unless the value of
 * operand1 equals the value of operand2.
 *
 * The OPERATOR_LESS_THAN operator is satisfied only if the value of
 * operand1 is ordered before the value of operand2.
 *
 * The OPERATOR_LESS_THAN_OR_EQUAL_TO operator is satisfied unless the value
 * of operand1 is ordered after the value of operand2.
 *
 * The OPERATOR_GREATER_THAN operator is satisfied only if the value of
 * operand1 is ordered after the value of operand2.
 *
 * The OPERATOR_GREATER_THAN_OR_EQUAL_TO operator is satisfied unless the
 * value of operand1 is ordered before the value of operand2.
 *
 * The OPERATOR_LIKE operator is satisfied only if the value of operand1
 * matches the pattern specified by the value of operand2, where in the pattern:
 * * the character "%" matches zero or more characters, and
 * * the character "_" (underscore) matches exactly one character, and
 * * the string "\x" matches the character "x", and
 *   all other characters match themselves.
 *
 * @api
 */
class Comparison_Original extends Constraint
{
    /**
     * @var DynamicOperand
     */
    protected $operand1;

    /**
     * @var integer
     */
    protected $operator;

    /**
     * @var mixed
     */
    protected $operand2;

    /**
     * Constructs this Comparison instance
     *
     * @param DynamicOperand $operand1
     * @param integer $operator one of \Neos\Flow\Persistence\QueryInterface.OPERATOR_*
     * @param mixed $operand2
     */
    public function __construct(DynamicOperand $operand1, $operator, $operand2 = null)
    {
        $this->operand1 = $operand1;
        $this->operator = $operator;
        $this->operand2 = $operand2;
    }

    /**
     *
     * Gets the first operand.
     *
     * @return DynamicOperand the operand; non-null
     * @api
     */
    public function getOperand1()
    {
        return $this->operand1;
    }

    /**
     * Gets the operator.
     *
     * @return integer one of \Neos\Flow\Persistence\QueryInterface.OPERATOR_*
     * @api
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * Gets the second operand.
     *
     * @return mixed
     * @api
     */
    public function getOperand2()
    {
        return $this->operand2;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Persistence\Generic\Qom;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Filters tuples based on the outcome of a binary operation.
 * 
 * For any comparison, operand2 always evaluates to a scalar value. In contrast,
 * operand1 may evaluate to an array of values (for example, the value of a multi-valued
 * property), in which case the comparison is separately performed for each element
 * of the array, and the Comparison constraint is satisfied as a whole if the
 * comparison against any element of the array is satisfied.
 * 
 * If operand1 and operand2 evaluate to values of different property types, the
 * value of operand2 is converted to the property type of the value of operand1.
 * If the type conversion fails, the query is invalid.
 * 
 * If operator is not supported for the property type of operand1, the query is invalid.
 * 
 * If operand1 evaluates to null (for example, if the operand evaluates the value
 * of a property which does not exist), the constraint is not satisfied.
 * 
 * The OPERATOR_EQUAL_TO operator is satisfied only if the value of operand1
 * equals the value of operand2.
 * 
 * The OPERATOR_NOT_EQUAL_TO operator is satisfied unless the value of
 * operand1 equals the value of operand2.
 * 
 * The OPERATOR_LESS_THAN operator is satisfied only if the value of
 * operand1 is ordered before the value of operand2.
 * 
 * The OPERATOR_LESS_THAN_OR_EQUAL_TO operator is satisfied unless the value
 * of operand1 is ordered after the value of operand2.
 * 
 * The OPERATOR_GREATER_THAN operator is satisfied only if the value of
 * operand1 is ordered after the value of operand2.
 * 
 * The OPERATOR_GREATER_THAN_OR_EQUAL_TO operator is satisfied unless the
 * value of operand1 is ordered before the value of operand2.
 * 
 * The OPERATOR_LIKE operator is satisfied only if the value of operand1
 * matches the pattern specified by the value of operand2, where in the pattern:
 * * the character "%" matches zero or more characters, and
 * * the character "_" (underscore) matches exactly one character, and
 * * the string "\x" matches the character "x", and
 *   all other characters match themselves.
 */
class Comparison extends Comparison_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait;


    /**
     * Autogenerated Proxy Method
     * @param DynamicOperand $operand1
     * @param integer $operator one of \Neos\Flow\Persistence\QueryInterface.OPERATOR_*
     * @param mixed $operand2
     */
    public function __construct()
    {
        $arguments = func_get_args();

        if (!array_key_exists(0, $arguments)) $arguments[0] = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Persistence\Generic\Qom\DynamicOperand');
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $operand1 in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(1, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $operator in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
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
  'operand1' => 'Neos\\Flow\\Persistence\\Generic\\Qom\\DynamicOperand',
  'operator' => 'integer',
  'operand2' => 'mixed',
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
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/Persistence/Generic/Qom/Comparison.php
#