<?php 

namespace AgzHack\Auth\Token;

use Neos\Flow\Security\Authentication\Token\AbstractToken;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Authentication\Token\SessionlessTokenInterface;

/**
 * An authentication token used for simple username and password authentication.
 */
class StatelessToken_Original extends AbstractToken implements SessionlessTokenInterface
{

    /**
     * The username/password credentials
     * @var array
     * @Flow\Transient
     */
    protected $credentials = array('token' => '');

    /**
     * Updates the token credentials from the POST vars, if the POST parameters
     * are available. Sets the authentication status to REAUTHENTICATION_NEEDED, if credentials have been sent.
     *
     * @param \Neos\Flow\Mvc\ActionRequest $actionRequest The current action request
     * @return void
     */
    public function updateCredentials(\Neos\Flow\Mvc\ActionRequest $actionRequest)
    {
        $token = $actionRequest->getHttpRequest()->getHeaders()->get('Key');

        if ($token != null) {
            $this->credentials = array('token' => $token);
            $this->setAuthenticationStatus(self::AUTHENTICATION_NEEDED);
        } else {
            $this->credentials = array('token' => null);
            $this->authenticationStatus = self::NO_CREDENTIALS_GIVEN;
        }
    }

    /**
     * Returns a string representation of the token for logging purposes.
     *
     * @return string The username credential
     */
    public function __toString()
    {
        return 'token: "' . $this->credentials['token'] . '"';
    }
}

#
# Start of Flow generated Proxy code
#
namespace AgzHack\Auth\Token;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * An authentication token used for simple username and password authentication.
 */
class StatelessToken extends StatelessToken_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __sleep()
    {
            $result = NULL;
        $this->Flow_Object_PropertiesToSerialize = array();

        $transientProperties = array (
  0 => 'credentials',
);
        $propertyVarTags = array (
  'credentials' => 'array',
  'authenticationProviderName' => 'string',
  'authenticationStatus' => 'integer',
  'account' => 'Neos\\Flow\\Security\\Account',
  'requestPatterns' => 'array',
  'entryPoint' => 'Neos\\Flow\\Security\\Authentication\\EntryPointInterface',
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
# PathAndFilename: /var/www/lux/Packages/Application/AgzHack.Auth/Classes/Token/StatelessToken.php
#