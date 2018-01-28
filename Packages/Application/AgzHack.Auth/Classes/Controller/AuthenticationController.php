<?php

namespace AgzHack\Auth\Controller;

/*
 * This file is part of the AgzHack.Auth package.
 */

use AgzHack\Auth\Service\AuthService;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Security\Authentication\Controller\AbstractAuthenticationController;
use Neos\Flow\Security\Authentication\TokenInterface;

class AuthenticationController extends AbstractAuthenticationController
{

    /**
     * @var AuthService
     * @Flow\Inject
     */
    protected $authService;


    /**
     * @param \Neos\Flow\Mvc\ActionRequest|null $originalRequest
     * @return string|void
     * @throws \Exception
     * @throws \Neos\Flow\Persistence\Exception\InvalidQueryException
     */
    public function onAuthenticationSuccess(\Neos\Flow\Mvc\ActionRequest $originalRequest = null)
    {
        $userAccount = $this->authService->getAuthenticatedUserAccount();

        $this->view->setVariablesToRender(array('userAccount'));

        $this->view->setConfiguration(
            array(
                'userAccount' => [
                    '_exposeObjectIdentifier' => true,
                    '_exposedObjectIdentifierKey' => '__identity',
                    '_only' => ['facebookId', 'name', 'email', 'avatar']
                ]
            )
        );

        $this->view->assign('userAccount', $userAccount);
    }
}
