<?php
/**
 * Created by PhpStorm.
 * User: thiago
 * Date: 28/01/18
 * Time: 01:15
 */

namespace AgzHack\Api\Controller;

use AgzHack\Auth\Domain\Repository\UserAccountRepository;
use Neos\Flow\Annotations as Flow;
use AgzHack\Auth\Service\AuthService;
use AgzHack\Lux\Service\LightMarkerService;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\Controller\RestController;
use Neos\Flow\Mvc\View\JsonView;
use Neos\Flow\Security\AccountFactory;
use Neos\Flow\Security\AccountRepository;
use Neos\Flow\Security\Authentication\AuthenticationProviderManager;
use Neos\Flow\Security\Authentication\AuthenticationProviderResolver;
use Neos\Flow\Security\Authentication\Controller\AbstractAuthenticationController;

class UsersController extends RestController
{
    protected $defaultViewObjectName = JsonView::class;

    protected $resourceArgumentName = 'user';

    /**
     * @var AuthService
     * @Flow\Inject
     */
    protected $authService;

    /**
     * @var AccountRepository
     * @Flow\Inject
     */
    protected $accountRepository;

    /**
     * @var AccountFactory
     * @Flow\Inject
     */
    protected $accountFactory;

    /**
     * @var UserAccountRepository
     * @Flow\Inject
     */
    protected $userAccountRepository;

    /**
     * @param string $token
     * @throws \Neos\Flow\Persistence\Exception\IllegalObjectTypeException
     */
    public function loginAction($token)
    {
        $this->view->setVariablesToRender(array('userAccount'));
        $this->view->setConfiguration(
            array(
                'userAccount' => [
                    '_exposeObjectIdentifier' => true,
                    '_exposedObjectIdentifierKey' => '__identity',
                    '_only' => ['facebookId', 'name', 'email', 'avatar'],
                ]
            )
        );

        $profile = $this->authService->authenticateFacebook($token);

        $userAccount = $this->authService->getUserAccount(
            $profile->identifier,
            $profile->email,
            $profile->displayName,
            $profile->photoURL
        );

        if (!$userAccount->hasValidAccount()) {
            $account = $this->getAccount($profile);
            $this->accountRepository->add($account);

            $userAccount->addAccount($account);
            $this->userAccountRepository->update($userAccount);
        }

        $this->view->assign('userAccount', $userAccount);
    }

    /**
     * @param $profile
     * @return \Neos\Flow\Security\Account
     */
    private function getAccount($profile)
    {
        $account = $this->accountFactory->createAccountWithPassword(
            $profile->identifier,
            $profile->identifier,
            array('AgzHack.Auth:Customer'),
            'DefaultProvider'
        );
        return $account;
    }
}
