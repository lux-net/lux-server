<?php 
namespace Neos\Flow\Security;

/*
 * This file is part of the Neos.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Security\Authentication\TokenInterface;
use Neos\Flow\Security\Exception\InvalidAuthenticationStatusException;
use Neos\Flow\Security\Policy\PolicyService;
use Neos\Flow\Security\Policy\Role;
use Neos\Flow\Utility\Now;

/**
 * An account model
 *
 * @Flow\Entity
 * @api
 */
class Account_Original
{
    /**
     * @var string
     * @Flow\Identity
     * @Flow\Validate(type="NotEmpty")
     * @Flow\Validate(type="StringLength", options={ "minimum"=1, "maximum"=255 })
     */
    protected $accountIdentifier;

    /**
     * @var string
     * @Flow\Identity
     * @Flow\Validate(type="NotEmpty")
     */
    protected $authenticationProviderName;

    /**
     * @var string
     * @ORM\Column(nullable=true)
     */
    protected $credentialsSource;

    /**
     * @var \DateTime
     */
    protected $creationDate;

    /**
     * @var \DateTime
     * @ORM\Column(nullable=true)
     */
    protected $expirationDate;

    /**
     * @var \DateTime
     * @ORM\Column(nullable=true)
     */
    protected $lastSuccessfulAuthenticationDate;

    /**
     * @var integer
     * @ORM\Column(nullable=true)
     */
    protected $failedAuthenticationCount;

    /**
     * @var array of strings
     * @ORM\Column(type="simple_array", nullable=true)
     */
    protected $roleIdentifiers = [];

    /**
     * @Flow\Transient
     * @var array<Role>
     */
    protected $roles;

    /**
     * @Flow\Inject
     * @var PolicyService
     */
    protected $policyService;

    /**
     * @Flow\Inject
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @Flow\Inject(lazy=false)
     * @var Now
     */
    protected $now;

    /**
     * Upon creation the creationDate property is initialized.
     */
    public function __construct()
    {
        $this->creationDate = new \DateTime();
    }

    /**
     * Initializes the roles field by fetching the role objects referenced by the roleIdentifiers
     *
     * @return void
     */
    protected function initializeRoles()
    {
        if ($this->roles !== null) {
            return;
        }
        $this->roles = [];
        foreach ($this->roleIdentifiers as $key => $roleIdentifier) {
            // check for and clean up roles no longer available
            if ($this->policyService->hasRole($roleIdentifier)) {
                $this->roles[$roleIdentifier] = $this->policyService->getRole($roleIdentifier);
            } else {
                unset($this->roleIdentifiers[$key]);
            }
        }
    }

    /**
     * Returns the account identifier
     *
     * @return string The account identifier
     * @api
     */
    public function getAccountIdentifier()
    {
        return $this->accountIdentifier;
    }

    /**
     * Set the account identifier
     *
     * @param string $accountIdentifier The account identifier
     * @return void
     * @api
     */
    public function setAccountIdentifier($accountIdentifier)
    {
        $this->accountIdentifier = $accountIdentifier;
    }

    /**
     * Returns the authentication provider name this account corresponds to
     *
     * @return string The authentication provider name
     * @api
     */
    public function getAuthenticationProviderName()
    {
        return $this->authenticationProviderName;
    }

    /**
     * Set the authentication provider name this account corresponds to
     *
     * @param string $authenticationProviderName The authentication provider name
     * @return void
     * @api
     */
    public function setAuthenticationProviderName($authenticationProviderName)
    {
        $this->authenticationProviderName = $authenticationProviderName;
    }

    /**
     * Returns the credentials source
     *
     * @return mixed The credentials source
     * @api
     */
    public function getCredentialsSource()
    {
        return $this->credentialsSource;
    }

    /**
     * Sets the credentials source
     *
     * @param mixed $credentialsSource The credentials source
     * @return void
     * @api
     */
    public function setCredentialsSource($credentialsSource)
    {
        $this->credentialsSource = $credentialsSource;
    }

    /**
     * Returns the roles this account has assigned
     *
     * @return array<Role> The assigned roles, indexed by role identifier
     * @api
     */
    public function getRoles()
    {
        $this->initializeRoles();
        return $this->roles;
    }

    /**
     * Sets the roles for this account
     *
     * @param array<Role> $roles An array of Policy\Role objects
     * @return void
     * @throws \InvalidArgumentException
     * @api
     */
    public function setRoles(array $roles)
    {
        $this->roleIdentifiers = [];
        $this->roles = [];
        foreach ($roles as $role) {
            if (!$role instanceof Role) {
                throw new \InvalidArgumentException(sprintf('setRoles() only accepts an array of %s instances, given: "%s"', Role::class, gettype($role)), 1397125997);
            }
            $this->addRole($role);
        }
    }

    /**
     * Return if the account has a certain role
     *
     * @param Role $role
     * @return boolean
     * @api
     */
    public function hasRole(Role $role)
    {
        $this->initializeRoles();
        return array_key_exists($role->getIdentifier(), $this->roles);
    }

    /**
     * Adds a role to this account
     *
     * @param Role $role
     * @return void
     * @throws \InvalidArgumentException
     * @api
     */
    public function addRole(Role $role)
    {
        if ($role->isAbstract()) {
            throw new \InvalidArgumentException(sprintf('Abstract roles can\'t be assigned to accounts directly, but the role "%s" is marked abstract', $role->getIdentifier()), 1399900657);
        }
        $this->initializeRoles();
        if (!$this->hasRole($role)) {
            $roleIdentifier = $role->getIdentifier();
            $this->roleIdentifiers[] = $roleIdentifier;
            $this->roles[$roleIdentifier] = $role;
        }
    }

    /**
     * Removes a role from this account
     *
     * @param Role $role
     * @return void
     * @api
     */
    public function removeRole(Role $role)
    {
        $this->initializeRoles();
        if ($this->hasRole($role)) {
            $roleIdentifier = $role->getIdentifier();
            unset($this->roles[$roleIdentifier]);
            $identifierIndex = array_search($roleIdentifier, $this->roleIdentifiers);
            unset($this->roleIdentifiers[$identifierIndex]);
        }
    }

    /**
     * Returns the date on which this account has been created.
     *
     * @return \DateTime
     * @api
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Returns the date on which this account has expired or will expire. If no expiration date has been set, NULL
     * is returned.
     *
     * @return \DateTime
     * @api
     */
    public function getExpirationDate()
    {
        return $this->expirationDate;
    }

    /**
     * Sets the date on which this account will become inactive
     *
     * @param \DateTime $expirationDate
     * @return void
     * @api
     */
    public function setExpirationDate(\DateTime $expirationDate = null)
    {
        $this->expirationDate = $expirationDate;
    }

    /**
     * @return integer
     * @api
     */
    public function getFailedAuthenticationCount()
    {
        return $this->failedAuthenticationCount;
    }

    /**
     * @return \DateTime
     * @api
     */
    public function getLastSuccessfulAuthenticationDate()
    {
        return $this->lastSuccessfulAuthenticationDate;
    }

    /**
     * Sets the authentication status. Usually called by the responsible \Neos\Flow\Security\Authentication\AuthenticationManagerInterface
     *
     * @param integer $authenticationStatus One of WRONG_CREDENTIALS, AUTHENTICATION_SUCCESSFUL
     * @return void
     * @throws InvalidAuthenticationStatusException
     */
    public function authenticationAttempted($authenticationStatus)
    {
        if ($authenticationStatus === TokenInterface::WRONG_CREDENTIALS) {
            $this->failedAuthenticationCount++;
        } elseif ($authenticationStatus === TokenInterface::AUTHENTICATION_SUCCESSFUL) {
            $this->lastSuccessfulAuthenticationDate = new \DateTime();
            $this->failedAuthenticationCount = 0;
        } else {
            throw new InvalidAuthenticationStatusException('Invalid authentication status.', 1449151375);
        }
    }

    /**
     * Returns TRUE if it is currently allowed to use this account for authentication.
     * Returns FALSE if the account has expired.
     *
     * @return boolean
     * @api
     */
    public function isActive()
    {
        return ($this->expirationDate === null || $this->expirationDate > $this->now);
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Security;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * An account model
 * @\Neos\Flow\Annotations\Entity
 */
class Account extends Account_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface, \Neos\Flow\Persistence\Aspect\PersistenceMagicInterface {

    use \Neos\Flow\Aop\AdvicesTrait, \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;

    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(length=40)
     * introduced by Neos\Flow\Persistence\Aspect\PersistenceMagicAspect
     */
    protected $Persistence_Object_Identifier = NULL;

    private $Flow_Aop_Proxy_targetMethodsAndGroupedAdvices = array();

    private $Flow_Aop_Proxy_groupedAdviceChains = array();

    private $Flow_Aop_Proxy_methodIsInAdviceMode = array();


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct'])) {
        parent::__construct();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct'] = TRUE;
            try {
            
                $methodArguments = [];

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__construct']['Neos\Flow\Aop\Advice\BeforeAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__construct']['Neos\Flow\Aop\Advice\BeforeAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Account', '__construct', $methodArguments);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Account', '__construct', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct']);
            return;
        }
        if ('Neos\Flow\Security\Account' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }
    }

    /**
     * Autogenerated Proxy Method
     */
    protected function Flow_Aop_Proxy_buildMethodsAndAdvicesArray()
    {
        if (method_exists(get_parent_class(), 'Flow_Aop_Proxy_buildMethodsAndAdvicesArray') && is_callable('parent::Flow_Aop_Proxy_buildMethodsAndAdvicesArray')) parent::Flow_Aop_Proxy_buildMethodsAndAdvicesArray();

        $objectManager = \Neos\Flow\Core\Bootstrap::$staticObjectManager;
        $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices = array(
            '__clone' => array(
                'Neos\Flow\Aop\Advice\BeforeAdvice' => array(
                    new \Neos\Flow\Aop\Advice\BeforeAdvice('Neos\Flow\Persistence\Aspect\PersistenceMagicAspect', 'generateUuid', $objectManager, NULL),
                ),
                'Neos\Flow\Aop\Advice\AfterReturningAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AfterReturningAdvice('Neos\Flow\Persistence\Aspect\PersistenceMagicAspect', 'cloneObject', $objectManager, NULL),
                ),
            ),
            '__construct' => array(
                'Neos\Flow\Aop\Advice\BeforeAdvice' => array(
                    new \Neos\Flow\Aop\Advice\BeforeAdvice('Neos\Flow\Persistence\Aspect\PersistenceMagicAspect', 'generateUuid', $objectManager, NULL),
                ),
            ),
        );
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
            $result = NULL;
        if (method_exists(get_parent_class(), '__wakeup') && is_callable('parent::__wakeup')) parent::__wakeup();
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __clone()
    {

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__clone'])) {
            $result = NULL;

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['__clone'] = TRUE;
            try {
            
                $methodArguments = [];

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['Neos\Flow\Aop\Advice\BeforeAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['Neos\Flow\Aop\Advice\BeforeAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Account', '__clone', $methodArguments);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Account', '__clone', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Account', '__clone', $methodArguments, NULL, $result);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__clone']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__clone']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __sleep()
    {
            $result = NULL;
        $this->Flow_Object_PropertiesToSerialize = array();

        $transientProperties = array (
  0 => 'roles',
);
        $propertyVarTags = array (
  'accountIdentifier' => 'string',
  'authenticationProviderName' => 'string',
  'credentialsSource' => 'string',
  'creationDate' => '\\DateTime',
  'expirationDate' => '\\DateTime',
  'lastSuccessfulAuthenticationDate' => '\\DateTime',
  'failedAuthenticationCount' => 'integer',
  'roleIdentifiers' => 'array of strings',
  'roles' => 'array<Neos\\Flow\\Security\\Policy\\Role>',
  'policyService' => 'Neos\\Flow\\Security\\Policy\\PolicyService',
  'objectManager' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
  'now' => 'Neos\\Flow\\Utility\\Now',
  'Persistence_Object_Identifier' => 'string',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Security\Policy\PolicyService', 'Neos\Flow\Security\Policy\PolicyService', 'policyService', '0b7a1e7038c946bf05af316d09b817a3', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Policy\PolicyService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ObjectManagement\ObjectManagerInterface', 'Neos\Flow\ObjectManagement\ObjectManager', 'objectManager', '9524ff5e5332c1890aa361e5d186b7b6', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'); });
        $this->now = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Utility\Now');
        $this->Flow_Injected_Properties = array (
  0 => 'policyService',
  1 => 'objectManager',
  2 => 'now',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/Security/Account.php
#