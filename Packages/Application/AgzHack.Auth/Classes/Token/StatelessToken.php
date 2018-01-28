<?php

namespace AgzHack\Auth\Token;

use Neos\Flow\Security\Authentication\Token\AbstractToken;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Authentication\Token\SessionlessTokenInterface;

/**
 * An authentication token used for simple username and password authentication.
 */
class StatelessToken extends AbstractToken implements SessionlessTokenInterface
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
