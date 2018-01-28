<?php

namespace AgzHack\Auth\Provider;

use AgzHack\Auth\Domain\Repository\UserAccountRepository;
use AgzHack\Auth\Token\StatelessToken;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Account;
use Neos\Flow\Security\Authentication\Provider\AbstractProvider;
use Neos\Flow\Security\Authentication\TokenInterface;
use Neos\Flow\Security\Exception\UnsupportedAuthenticationTokenException;

/**
 * An authentication provider that authenticates
 * Neos\Flow\Security\Authentication\Token\UsernamePassword tokens.
 * The accounts are stored in the Content Repository.
 */
class StatelessTokenProvider extends AbstractProvider
{

    /**
     * @var \Neos\Flow\Security\AccountRepository
     * @Flow\Inject
     */
    protected $accountRepository;

    /**
     * @var \Neos\Flow\Security\Cryptography\HashService
     * @Flow\Inject
     */
    protected $hashService;

    /**
     * @var \Neos\Flow\Security\Context
     * @Flow\Inject
     */
    protected $securityContext;

    /**
     * @var \Neos\Flow\Persistence\PersistenceManagerInterface
     * @Flow\Inject
     */
    protected $persistenceManager;

    /**
     * Returns the class names of the tokens this provider can authenticate.
     *
     * @return array
     */
    public function getTokenClassNames()
    {
        return array(StatelessToken::class);
    }

    /**
     * @param TokenInterface $authenticationToken
     * @throws UnsupportedAuthenticationTokenException
     * @throws \Exception
     */
    public function authenticate(TokenInterface $authenticationToken)
    {
        if (!($authenticationToken instanceof StatelessToken)) {
            throw new UnsupportedAuthenticationTokenException(
                'This provider cannot authenticate the given token.',
                1217339840
            );
        }
        $credentials = $authenticationToken->getCredentials();

        if (is_array($credentials) && isset($credentials['token']) && $credentials['token'] != null) {
            $this->authenticateWithToken($authenticationToken);
        }
    }

    /**
     * @param TokenInterface $authenticationToken
     * @throws \Exception
     */
    protected function authenticateWithToken(TokenInterface $authenticationToken)
    {
        $credentials = $authenticationToken->getCredentials();
        $account = null;

        $providerName = $this->name;
        $accountRepository = $this->accountRepository;


        $this->securityContext->withoutAuthorizationChecks(function () use ($credentials, $providerName, $accountRepository, &$account) {
            $account = $accountRepository->findActiveByAccountIdentifierAndAuthenticationProviderName($credentials['token'], 'DefaultProvider');
        });

        if ($account instanceof Account) {
            $authenticationToken->setAccount($account);
            $authenticationToken->setAuthenticationStatus(TokenInterface::AUTHENTICATION_SUCCESSFUL);
        } else {
            $authenticationToken->setAuthenticationStatus(TokenInterface::WRONG_CREDENTIALS);
        }
    }
}
