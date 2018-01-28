<?php 
namespace Neos\Flow\Security\Authorization\Privilege\Entity\Doctrine;

/*
 * This file is part of the Neos.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping\QuoteStrategy;
use Doctrine\ORM\Query\Filter\SQLFilter as DoctrineSqlFilter;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Persistence\Doctrine\PersistenceManager;
use Neos\Flow\Persistence\Doctrine\Query;
use Neos\Utility\ObjectAccess;
use Neos\Flow\Security\Context;
use Neos\Flow\Security\Exception\InvalidPolicyException;
use Neos\Flow\Security\Exception\InvalidQueryRewritingConstraintException;
use Neos\Flow\Security\Policy\PolicyService;
use Neos\Utility\TypeHandling;

/**
 * A sql generator to create a sql condition for an entity property.
 */
class PropertyConditionGenerator_Original implements SqlGeneratorInterface
{
    /**
     * Property path the currently parsed expression relates to
     *
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $operator;

    /**
     * @var string|array
     */
    protected $operandDefinition;

    /**
     * @var mixed
     */
    protected $operand;

    /**
     * Array of registered global objects that can be accessed as operands
     *
     * @Flow\InjectConfiguration("aop.globalObjects")
     * @var array
     */
    protected $globalObjects = [];

    /**
     * @Flow\Inject
     * @var Context
     */
    protected $securityContext;

    /**
     * @Flow\Inject
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @Flow\Inject
     * @var PolicyService
     */
    protected $policyService;

    /**
     * @Flow\Inject
     * @var ObjectManager
     */
    protected $entityManager;

    /**
     * @Flow\Inject
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * Raw parameter values
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * @param string $path Property path the currently parsed expression relates to
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * @param string|array $operandDefinition
     * @return PropertyConditionGenerator the current instance to allow for method chaining
     */
    public function equals($operandDefinition)
    {
        $this->operator = '==';
        $this->operandDefinition = $operandDefinition;
        $this->operand = $this->getValueForOperand($operandDefinition);
        return $this;
    }

    /**
     * @param string|array $operandDefinition
     * @return PropertyConditionGenerator the current instance to allow for method chaining
     */
    public function notEquals($operandDefinition)
    {
        $this->operator = '!=';
        $this->operandDefinition = $operandDefinition;
        $this->operand = $this->getValueForOperand($operandDefinition);
        return $this;
    }

    /**
     * @param mixed $operandDefinition
     * @return PropertyConditionGenerator the current instance to allow for method chaining
     */
    public function lessThan($operandDefinition)
    {
        $this->operator = '<';
        $this->operandDefinition = $operandDefinition;
        $this->operand = $this->getValueForOperand($operandDefinition);
        return $this;
    }

    /**
     * @param mixed $operandDefinition
     * @return PropertyConditionGenerator the current instance to allow for method chaining
     */
    public function lessOrEqual($operandDefinition)
    {
        $this->operator = '<=';
        $this->operandDefinition = $operandDefinition;
        $this->operand = $this->getValueForOperand($operandDefinition);
        return $this;
    }

    /**
     * @param mixed $operandDefinition
     * @return PropertyConditionGenerator the current instance to allow for method chaining
     */
    public function greaterThan($operandDefinition)
    {
        $this->operator = '>';
        $this->operandDefinition = $operandDefinition;
        $this->operand = $this->getValueForOperand($operandDefinition);
        return $this;
    }

    /**
     * @param mixed $operandDefinition
     * @return PropertyConditionGenerator the current instance to allow for method chaining
     */
    public function greaterOrEqual($operandDefinition)
    {
        $this->operator = '>=';
        $this->operandDefinition = $operandDefinition;
        $this->operand = $this->getValueForOperand($operandDefinition);
        return $this;
    }

    /**
     * @param mixed $operandDefinition
     * @return PropertyConditionGenerator the current instance to allow for method chaining
     */
    public function like($operandDefinition)
    {
        $this->operator = 'like';
        $this->operandDefinition = $operandDefinition;
        $this->operand = $this->getValueForOperand($operandDefinition);
        return $this;
    }

    /**
     * @param mixed $operandDefinition
     * @return PropertyConditionGenerator the current instance to allow for method chaining
     * @throws InvalidPolicyException
     */
    public function in($operandDefinition)
    {
        $this->operator = 'in';
        $this->operand = $this->getValueForOperand($operandDefinition);

        if (is_array($this->operand) === false && ($this->operand instanceof \Traversable) === false) {
            throw new InvalidPolicyException(sprintf('The "in" operator needs an array as operand! Got: "%s"', $this->operand), 1416313526);
        }
        foreach ($this->operand as $iterator => $singleOperandValueDefinition) {
            $this->operandDefinition['inOperandValue' . $iterator] = $singleOperandValueDefinition;
        }
        return $this;
    }

    /**
     * @param DoctrineSqlFilter $sqlFilter
     * @param ClassMetadata $targetEntity
     * @param string $targetTableAlias
     * @return string
     * @throws InvalidQueryRewritingConstraintException
     * @throws \Exception
     */
    public function getSql(DoctrineSqlFilter $sqlFilter, ClassMetadata $targetEntity, $targetTableAlias)
    {
        $targetEntityPropertyName = (strpos($this->path, '.') ? substr($this->path, 0, strpos($this->path, '.')) : $this->path);
        $quoteStrategy = $this->entityManager->getConfiguration()->getQuoteStrategy();

        if ($targetEntity->hasAssociation($targetEntityPropertyName) === false) {
            return $this->getSqlForSimpleProperty($sqlFilter, $quoteStrategy, $targetEntity, $targetTableAlias, $targetEntityPropertyName);
        } elseif (strstr($this->path, '.') === false && $targetEntity->isSingleValuedAssociation($targetEntityPropertyName) === true && $targetEntity->isAssociationInverseSide($targetEntityPropertyName) === false) {
            return $this->getSqlForManyToOneAndOneToOneRelationsWithoutPropertyPath($sqlFilter, $quoteStrategy, $targetEntity, $targetTableAlias, $targetEntityPropertyName);
        } elseif ($targetEntity->isSingleValuedAssociation($targetEntityPropertyName) === true && $targetEntity->isAssociationInverseSide($targetEntityPropertyName) === false) {
            return $this->getSqlForManyToOneAndOneToOneRelationsWithPropertyPath($sqlFilter, $quoteStrategy, $targetEntity, $targetTableAlias, $targetEntityPropertyName);
        } elseif ($targetEntity->isSingleValuedAssociation($targetEntityPropertyName) === true && $targetEntity->isAssociationInverseSide($targetEntityPropertyName) === true) {
            throw new InvalidQueryRewritingConstraintException('Single valued properties from the inverse side are not supported in a content security constraint path! Got: "' . $this->path . ' ' . $this->operator . ' ' . $this->operandDefinition . '"', 1416397754);
        } elseif ($targetEntity->isCollectionValuedAssociation($targetEntityPropertyName) === true) {
            throw new InvalidQueryRewritingConstraintException('Multivalued properties are not supported in a content security constraint path! Got: "' . $this->path . ' ' . $this->operator . ' ' . $this->operandDefinition . '"', 1416397655);
        }

        throw new InvalidQueryRewritingConstraintException('The configured operator of the entity constraint is not valid/supported. Got: ' . $this->operator, 1270483540);
    }

    /**
     * @param DoctrineSqlFilter $sqlFilter
     * @param QuoteStrategy $quoteStrategy
     * @param ClassMetadata $targetEntity
     * @param string $targetTableAlias
     * @param string $targetEntityPropertyName
     * @return string
     * @throws InvalidQueryRewritingConstraintException
     * @throws \Exception
     */
    protected function getSqlForSimpleProperty(DoctrineSqlFilter $sqlFilter, QuoteStrategy $quoteStrategy, ClassMetadata $targetEntity, $targetTableAlias, $targetEntityPropertyName)
    {
        $quotedColumnName = $quoteStrategy->getColumnName($targetEntityPropertyName, $targetEntity, $this->entityManager->getConnection()->getDatabasePlatform());
        $propertyPointer = $targetTableAlias . '.' . $quotedColumnName;

        if (is_array($this->operandDefinition)) {
            foreach ($this->operandDefinition as $operandIterator => $singleOperandValue) {
                $this->setParameter($sqlFilter, $operandIterator, $singleOperandValue);
            }
        } else {
            $this->setParameter($sqlFilter, $this->operandDefinition, $this->operand);
        }
        return $this->getConstraintStringForSimpleProperty($sqlFilter, $propertyPointer);
    }

    /**
     * @param DoctrineSqlFilter $sqlFilter
     * @param QuoteStrategy $quoteStrategy
     * @param ClassMetadata $targetEntity
     * @param string $targetTableAlias
     * @param string $targetEntityPropertyName
     * @return string
     * @throws InvalidQueryRewritingConstraintException
     * @throws \Exception
     */
    protected function getSqlForManyToOneAndOneToOneRelationsWithoutPropertyPath(DoctrineSqlFilter $sqlFilter, QuoteStrategy $quoteStrategy, ClassMetadata $targetEntity, $targetTableAlias, $targetEntityPropertyName)
    {
        $associationMapping = $targetEntity->getAssociationMapping($targetEntityPropertyName);

        $constraints = [];
        foreach ($associationMapping['joinColumns'] as $joinColumn) {
            $quotedColumnName = $quoteStrategy->getJoinColumnName($joinColumn, $targetEntity, $this->entityManager->getConnection()->getDatabasePlatform());
            $propertyPointer = $targetTableAlias . '.' . $quotedColumnName;

            $operandAlias = $this->operandDefinition;
            if (is_array($this->operandDefinition)) {
                $operandAlias = key($this->operandDefinition);
            }
            $currentReferencedOperandName = $operandAlias . $joinColumn['referencedColumnName'];
            if (is_object($this->operand)) {
                $operandMetadataInfo = $this->entityManager->getClassMetadata(TypeHandling::getTypeForValue($this->operand));
                $currentReferencedValueOfOperand = $operandMetadataInfo->getFieldValue($this->operand, $operandMetadataInfo->getFieldForColumn($joinColumn['referencedColumnName']));
                $this->setParameter($sqlFilter, $currentReferencedOperandName, $currentReferencedValueOfOperand, $associationMapping['type']);
            } elseif (is_array($this->operandDefinition)) {
                foreach ($this->operandDefinition as $operandIterator => $singleOperandValue) {
                    if (is_object($singleOperandValue)) {
                        $operandMetadataInfo = $this->entityManager->getClassMetadata(TypeHandling::getTypeForValue($singleOperandValue));
                        $currentReferencedValueOfOperand = $operandMetadataInfo->getFieldValue($singleOperandValue, $operandMetadataInfo->getFieldForColumn($joinColumn['referencedColumnName']));
                        $this->setParameter($sqlFilter, $operandIterator, $currentReferencedValueOfOperand, $associationMapping['type']);
                    } elseif ($singleOperandValue === null) {
                        $this->setParameter($sqlFilter, $operandIterator, null, $associationMapping['type']);
                    }
                }
            }

            $constraints[] = $this->getConstraintStringForSimpleProperty($sqlFilter, $propertyPointer, $currentReferencedOperandName);
        }
        return ' (' . implode(' ) AND ( ', $constraints) . ') ';
    }

    /**
     * @param DoctrineSqlFilter $sqlFilter
     * @param QuoteStrategy $quoteStrategy
     * @param ClassMetadata $targetEntity
     * @param string $targetTableAlias
     * @param string $targetEntityPropertyName
     * @return string
     * @throws InvalidQueryRewritingConstraintException
     * @throws \Exception
     */
    protected function getSqlForManyToOneAndOneToOneRelationsWithPropertyPath(DoctrineSqlFilter $sqlFilter, QuoteStrategy $quoteStrategy, ClassMetadata $targetEntity, $targetTableAlias, $targetEntityPropertyName)
    {
        $subselectQuery = $this->getSubselectQuery($targetEntity, $targetEntityPropertyName);

        $associationMapping = $targetEntity->getAssociationMapping($targetEntityPropertyName);

        $subselectConstraintQueries = [];
        foreach ($associationMapping['joinColumns'] as $joinColumn) {
            $rootAliases = $subselectQuery->getQueryBuilder()->getRootAliases();
            $subselectQuery->getQueryBuilder()->select($rootAliases[0] . '.' . $targetEntity->getFieldForColumn($joinColumn['referencedColumnName']));
            $subselectSql = $subselectQuery->getSql();
            foreach ($subselectQuery->getParameters() as $parameter) {
                $parameterValue = $parameter->getValue();
                if (is_object($parameterValue)) {
                    $parameterValue = $this->persistenceManager->getIdentifierByObject($parameter->getValue());
                }
                $subselectSql = preg_replace('/\?/', $this->entityManager->getConnection()->quote($parameterValue, $parameter->getType()), $subselectSql, 1);
            }
            $quotedColumnName = $quoteStrategy->getJoinColumnName($joinColumn, $targetEntity, $this->entityManager->getConnection()->getDatabasePlatform());
            $subselectIdentifier = 'subselect' . md5($subselectSql);
            $subselectConstraintQueries[] = $targetTableAlias . '.' . $quotedColumnName . ' IN (SELECT ' . $subselectIdentifier . '.' . $joinColumn['referencedColumnName'] . '_0 FROM (' . $subselectSql . ') AS ' . $subselectIdentifier . ' ) ';
        }

        return ' (' . implode(' ) AND ( ', $subselectConstraintQueries) . ') ';
    }

    /**
     * @param ClassMetadata $targetEntity
     * @param string $targetEntityPropertyName
     * @return Query
     */
    protected function getSubselectQuery(ClassMetadata $targetEntity, $targetEntityPropertyName)
    {
        $subselectQuery = new Query($targetEntity->getAssociationTargetClass($targetEntityPropertyName));
        $propertyName = str_replace($targetEntityPropertyName . '.', '', $this->path);

        switch ($this->operator) {
            case '==':
                $subselectConstraint = $subselectQuery->equals($propertyName, $this->operand);
                break;
            case '!=':
                $subselectConstraint = $subselectQuery->logicalNot($subselectQuery->equals($propertyName, $this->operand));
                break;
            case '<':
                $subselectConstraint = $subselectQuery->lessThan($propertyName, $this->operand);
                break;
            case '>':
                $subselectConstraint = $subselectQuery->greaterThan($propertyName, $this->operand);
                break;
            case '<=':
                $subselectConstraint = $subselectQuery->lessThanOrEqual($propertyName, $this->operand);
                break;
            case '>=':
                $subselectConstraint = $subselectQuery->greaterThanOrEqual($propertyName, $this->operand);
                break;
            case 'like':
                $subselectConstraint = $subselectQuery->like($propertyName, $this->operand);
                break;
            case 'in':
                $subselectConstraint = $subselectQuery->in($propertyName, $this->operand);
                break;
        }

        $subselectQuery->matching($subselectConstraint);
        return $subselectQuery;
    }

    /**
     * @param SQLFilter $sqlFilter
     * @param string $propertyPointer
     * @param string $operandDefinition
     * @return string
     */
    protected function getConstraintStringForSimpleProperty(SQLFilter $sqlFilter, $propertyPointer, $operandDefinition = null)
    {
        $operandDefinition = ($operandDefinition === null ? $this->operandDefinition : $operandDefinition);
        $parameter = null;
        $addNullExpression = false;
        try {
            if (is_array($this->operandDefinition)) {
                $parameters = [];
                foreach ($this->operandDefinition as $operandIterator => $singleOperandValue) {
                    if ($singleOperandValue !== null) {
                        $parameters[] = $sqlFilter->getParameter($operandIterator);
                    } else {
                        $addNullExpression = true;
                    }
                }
                $parameter = implode(',', $parameters);
            } elseif ($this->getRawParameterValue($operandDefinition) !== null) {
                $parameter = $sqlFilter->getParameter($operandDefinition);
            }
        } catch (\InvalidArgumentException $exception) {
        }

        if ($parameter === null || $parameter === '') {
            $addNullExpression = true;
        }

        switch ($this->operator) {
            case '==':
                return ($parameter === null ? $propertyPointer . ' IS NULL' : $propertyPointer . ' = ' . $parameter);
            case '!=':
                return ($parameter === null ? $propertyPointer . ' IS NOT NULL' : $propertyPointer . ' <> ' . $parameter);
            case '<':
                return $propertyPointer . ' < ' . $parameter;
            case '>':
                return $propertyPointer . ' > ' . $parameter;
            case '<=':
                return $propertyPointer . ' <= ' . $parameter;
            case '>=':
                return $propertyPointer . ' >= ' . $parameter;
            case 'like':
                return $propertyPointer . ' LIKE ' . $parameter;
            case 'in':
                $inPart = $parameter !== null && $parameter !== '' ? $propertyPointer . ' IN (' . $parameter . ') ' : '';
                $nullPart = $addNullExpression ? $propertyPointer . ' IS NULL' : '';
                return $inPart . ($inPart !== '' && $nullPart !== '' ? ' OR ' : '') . $nullPart;
        }
    }

    /**
     * Returns the static value of the given operand, this might be also a global object
     *
     * @param mixed $expression The expression string representing the operand
     * @return mixed The calculated value
     */
    public function getValueForOperand($expression)
    {
        if (is_array($expression)) {
            $result = [];
            foreach ($expression as $expressionEntry) {
                $result[] = $this->getValueForOperand($expressionEntry);
            }
            return $result;
        } elseif (is_numeric($expression)) {
            return $expression;
        } elseif ($expression === true) {
            return true;
        } elseif ($expression === false) {
            return false;
        } elseif ($expression === null) {
            return null;
        } elseif (strpos($expression, 'context.') === 0) {
            $objectAccess = explode('.', $expression, 3);
            $globalObjectsRegisteredClassName = $this->globalObjects[$objectAccess[1]];
            $globalObject = $this->objectManager->get($globalObjectsRegisteredClassName);
            $this->securityContext->withoutAuthorizationChecks(function () use ($globalObject, $objectAccess, &$globalObjectValue) {
                $globalObjectValue = $this->getObjectValueByPath($globalObject, $objectAccess[2]);
            });

            return $globalObjectValue;
        } else {
            return trim($expression, '"\'');
        }
    }

    /**
     * @param DoctrineSqlFilter $sqlFilter
     * @param mixed $name
     * @param mixed $value
     * @param string $type
     * @return void
     */
    protected function setParameter(DoctrineSqlFilter $sqlFilter, $name, $value, $type = null)
    {
        $sqlFilter->setParameter($name, $value, $type);
        $this->parameters[$name] = $value;
    }

    /**
     * @param mixed $name
     * @return mixed the raw parameter value
     */
    protected function getRawParameterValue($name)
    {
        if (isset($this->parameters[$name]) === false) {
            throw new \InvalidArgumentException('Paremeter "' . $name . '" does not exist.', 1435830434);
        }

        return $this->parameters[$name];
    }

    /**
     * Redirects directly to \Neos\Utility\ObjectAccess::getPropertyPath($result, $propertyPath)
     * This is only needed for unit tests!
     *
     * @param mixed $object The object to fetch the property from
     * @param string $path The path to the property to be fetched
     * @return mixed The property value
     */
    public function getObjectValueByPath($object, $path)
    {
        return ObjectAccess::getPropertyPath($object, $path);
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Security\Authorization\Privilege\Entity\Doctrine;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A sql generator to create a sql condition for an entity property.
 */
class PropertyConditionGenerator extends PropertyConditionGenerator_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     * @param string $path Property path the currently parsed expression relates to
     */
    public function __construct()
    {
        $arguments = func_get_args();
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $path in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        call_user_func_array('parent::__construct', $arguments);
        if ('Neos\Flow\Security\Authorization\Privilege\Entity\Doctrine\PropertyConditionGenerator' === get_class($this)) {
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
  'path' => 'string',
  'operator' => 'string',
  'operandDefinition' => 'string|array',
  'operand' => 'mixed',
  'globalObjects' => 'array',
  'securityContext' => 'Neos\\Flow\\Security\\Context',
  'objectManager' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
  'policyService' => 'Neos\\Flow\\Security\\Policy\\PolicyService',
  'entityManager' => 'Doctrine\\Common\\Persistence\\ObjectManager',
  'persistenceManager' => 'Neos\\Flow\\Persistence\\Doctrine\\PersistenceManager',
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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Security\Context', 'Neos\Flow\Security\Context', 'securityContext', 'f7e2ddeaebd191e228b8c2e4dc7f1f83', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Context'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ObjectManagement\ObjectManagerInterface', 'Neos\Flow\ObjectManagement\ObjectManager', 'objectManager', '9524ff5e5332c1890aa361e5d186b7b6', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Security\Policy\PolicyService', 'Neos\Flow\Security\Policy\PolicyService', 'policyService', '0b7a1e7038c946bf05af316d09b817a3', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Policy\PolicyService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Doctrine\Common\Persistence\ObjectManager', 'Doctrine\Common\Persistence\ObjectManager', 'entityManager', 'b34c32b6d660d4fb8aaafae6c0286b19', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Doctrine\Common\Persistence\ObjectManager'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Persistence\Doctrine\PersistenceManager', 'Neos\Flow\Persistence\Doctrine\PersistenceManager', 'persistenceManager', '90135528ef7af4a013b4d45f90addf22', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Persistence\Doctrine\PersistenceManager'); });
        $this->globalObjects = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Flow.aop.globalObjects');
        $this->Flow_Injected_Properties = array (
  0 => 'securityContext',
  1 => 'objectManager',
  2 => 'policyService',
  3 => 'entityManager',
  4 => 'persistenceManager',
  5 => 'globalObjects',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/Security/Authorization/Privilege/Entity/Doctrine/PropertyConditionGenerator.php
#