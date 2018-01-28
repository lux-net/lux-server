<?php 
namespace Neos\Flow\Mvc\Controller;

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
use Neos\Error\Messages\Result;
use Neos\Flow\Property\PropertyMapper;
use Neos\Utility\TypeHandling;
use Neos\Flow\Validation\Validator\ValidatorInterface;

/**
 * A controller argument
 *
 * @api
 */
class Argument_Original
{
    /**
     * Name of this argument
     * @var string
     */
    protected $name = '';

    /**
     * Data type of this argument's value
     * @var string
     */
    protected $dataType = null;

    /**
     * TRUE if this argument is required
     * @var boolean
     */
    protected $isRequired = false;

    /**
     * Actual value of this argument
     * @var object
     */
    protected $value = null;

    /**
     * Default value. Used if argument is optional.
     * @var mixed
     */
    protected $defaultValue = null;

    /**
     * A custom validator, used supplementary to the base validation
     * @var ValidatorInterface
     */
    protected $validator = null;

    /**
     * The validation results. This can be asked if the argument has errors.
     * @var Result
     */
    protected $validationResults = null;

    /**
     * @var MvcPropertyMappingConfiguration
     */
    protected $propertyMappingConfiguration;

    /**
     * @Flow\Inject
     * @var PropertyMapper
     */
    protected $propertyMapper;

    /**
     * Constructs this controller argument
     *
     * @param string $name Name of this argument
     * @param string $dataType The data type of this argument
     * @throws \InvalidArgumentException if $name is not a string or empty
     * @api
     */
    public function __construct($name, $dataType)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException('$name must be of type string, ' . gettype($name) . ' given.', 1187951688);
        }
        if (strlen($name) === 0) {
            throw new \InvalidArgumentException('$name must be a non-empty string, ' . strlen($name) . ' characters given.', 1232551853);
        }
        $this->name = $name;
        $this->dataType = TypeHandling::normalizeType($dataType);
    }

    /**
     * Returns the name of this argument
     *
     * @return string This argument's name
     * @api
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the data type of this argument's value
     *
     * @return string The data type
     * @api
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * Marks this argument to be required
     *
     * @param boolean $required TRUE if this argument should be required
     * @return Argument $this
     * @api
     */
    public function setRequired($required)
    {
        $this->isRequired = (boolean)$required;
        return $this;
    }

    /**
     * Returns TRUE if this argument is required
     *
     * @return boolean TRUE if this argument is required
     * @api
     */
    public function isRequired()
    {
        return $this->isRequired;
    }

    /**
     * Sets the default value of the argument
     *
     * @param mixed $defaultValue Default value
     * @return Argument $this
     * @api
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    /**
     * Returns the default value of this argument
     *
     * @return mixed The default value
     * @api
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Sets a custom validator which is used supplementary to the base validation
     *
     * @param ValidatorInterface $validator The actual validator object
     * @return Argument Returns $this (used for fluent interface)
     * @api
     */
    public function setValidator(ValidatorInterface $validator)
    {
        $this->validator = $validator;
        return $this;
    }

    /**
     * Returns the set validator
     *
     * @return ValidatorInterface The set validator, NULL if none was set
     * @api
     */
    public function getValidator()
    {
        return $this->validator;
    }

    /**
     * Sets the value of this argument.
     *
     * @param mixed $rawValue The value of this argument
     * @return Argument $this
     */
    public function setValue($rawValue)
    {
        if ($rawValue === null) {
            $this->value = null;
            return $this;
        }
        if (is_object($rawValue) && $rawValue instanceof $this->dataType) {
            $this->value = $rawValue;
            return $this;
        }
        $this->value = $this->propertyMapper->convert($rawValue, $this->dataType, $this->getPropertyMappingConfiguration());
        $this->validationResults = $this->propertyMapper->getMessages();
        if ($this->validator !== null) {
            $validationMessages = $this->validator->validate($this->value);
            $this->validationResults->merge($validationMessages);
        }

        return $this;
    }

    /**
     * Returns the value of this argument. If the value is NULL, we use the defaultValue.
     *
     * @return object The value of this argument - if none was set, the default value is returned
     * @api
     */
    public function getValue()
    {
        return ($this->value === null) ? $this->defaultValue : $this->value;
    }

    /**
     * Return the Property Mapping Configuration used for this argument; can be used by the initialize*action to modify the Property Mapping.
     *
     * @return MvcPropertyMappingConfiguration
     * @api
     */
    public function getPropertyMappingConfiguration()
    {
        if ($this->propertyMappingConfiguration === null) {
            $this->propertyMappingConfiguration = new MvcPropertyMappingConfiguration();
        }
        return $this->propertyMappingConfiguration;
    }

    /**
     * @return array<Neos\Error\Messages\Result> Validation errors which have occured.
     * @api
     */
    public function getValidationResults()
    {
        return $this->validationResults;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Mvc\Controller;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A controller argument
 */
class Argument extends Argument_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     * @param string $name Name of this argument
     * @param string $dataType The data type of this argument
     * @throws \InvalidArgumentException if $name is not a string or empty
     */
    public function __construct()
    {
        $arguments = func_get_args();
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $name in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(1, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $dataType in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        call_user_func_array('parent::__construct', $arguments);
        if ('Neos\Flow\Mvc\Controller\Argument' === get_class($this)) {
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
  'name' => 'string',
  'dataType' => 'string',
  'isRequired' => 'boolean',
  'value' => 'object',
  'defaultValue' => 'mixed',
  'validator' => 'Neos\\Flow\\Validation\\Validator\\ValidatorInterface',
  'validationResults' => 'Neos\\Error\\Messages\\Result',
  'propertyMappingConfiguration' => 'Neos\\Flow\\Mvc\\Controller\\MvcPropertyMappingConfiguration',
  'propertyMapper' => 'Neos\\Flow\\Property\\PropertyMapper',
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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Property\PropertyMapper', 'Neos\Flow\Property\PropertyMapper', 'propertyMapper', '2ab4a1ce2ee31715713d0f207f0ac637', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Property\PropertyMapper'); });
        $this->Flow_Injected_Properties = array (
  0 => 'propertyMapper',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/Mvc/Controller/Argument.php
#