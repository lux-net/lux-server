<?php

namespace AgzHack\Auth\Domain\Repository;

use AgzHack\Auth\Domain\Model\UserAccount;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Repository;
use Neos\Flow\Security\Account;

/**
 * @Flow\Scope("singleton")
 */
class UserAccountRepository extends Repository
{


    /**
     * @param Account $account
     * @return UserAccount
     * @throws \Neos\Flow\Persistence\Exception\InvalidQueryException
     */
    public function findOneHavingAccount(Account $account)
    {
        $query = $this->createQuery();

        return $query->matching($query->contains('accounts', $account))->execute()->getFirst();
    }
}
