<?php

namespace AgzHack\Auth\Service;

use AgzHack\Auth\Domain\Model\UserAccount;
use AgzHack\Auth\Domain\Repository\UserAccountRepository;
use Hybridauth\Hybridauth;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\AccountFactory;
use Neos\Flow\Security\AccountRepository;
use Neos\Flow\Security\Authentication\TokenInterface;

/**
 * @Flow\Scope("singleton")
 */
class AuthService
{

    /**
     * @var UserAccountRepository
     * @Flow\Inject
     */
    protected $userAccountRepository;

    /**
     * @Flow\Inject
     * @var \Neos\Flow\Security\Context
     */
    protected $securityContext;


    protected $config = [
        //Location where to redirect users once they authenticate with Facebook
        //For this example we choose to come back to this same script
        'callback' => 'http://localhost:1003/users',

        //Facebook application credentials
        'keys' => [
            //Required: your Facebook application id
            'id' => '525134521192229',

            //Required: your Facebook application secret
            'secret' => 'b7599fe8a219de5cb573bb3ed5a90bf1'
        ]
    ];

    /**
     * @return \Hybridauth\User\Profile
     */
    public function authenticateFacebook($token)
    {
        //Instantiate Facebook's adapter directly
        $adapter = new \Hybridauth\Provider\Facebook($this->config);

        $adapter->setAccessToken(['access_token' => $token]);
        $userProfile = $adapter->getUserProfile();
        $adapter->disconnect();
        return $userProfile;
    }

    /**
     * @param string $facebookId
     * @param string $email
     * @param string $name
     * @param string $avatar
     * @return UserAccount
     * @throws \Neos\Flow\Persistence\Exception\IllegalObjectTypeException
     */
    public function getUserAccount($facebookId, $email, $name, $avatar)
    {
        $userAccount = $this->userAccountRepository->findOneByEmail($email);
        if ($userAccount instanceof UserAccount) {
            return $userAccount;
        }

        $userAccount = new UserAccount($facebookId, $email, $name, $avatar);
        $this->userAccountRepository->add($userAccount);

        return $userAccount;
    }

    /**
     * @return \Neos\Flow\Security\Account
     * @throws \Exception
     */
    public function getAuthenticatedAccount()
    {
        $tokens = $this->securityContext->getAuthenticationTokens();
        /** @var TokenInterface $token */
        foreach ($tokens as $token) {
            if ($token->isAuthenticated()) {
                return $token->getAccount();
            }
        }

        throw new \Exception("No active token", 1517142991);
    }

    /**
     * @return UserAccount
     * @throws \Exception
     * @throws \Neos\Flow\Persistence\Exception\InvalidQueryException
     */
    public function getAuthenticatedUserAccount()
    {
        $authenticatedAccount = $this->getAuthenticatedAccount();
        return $this->userAccountRepository->findOneHavingAccount($authenticatedAccount);
    }
}
