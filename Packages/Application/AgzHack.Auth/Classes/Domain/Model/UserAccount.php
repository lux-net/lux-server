<?php

namespace AgzHack\Auth\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Security\Account;

/**
 * @Flow\Entity
 */
class UserAccount
{

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $avatar;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $facebookId;


    /**
     * @var Collection<\Neos\Flow\Security\Account>
     * @ORM\ManyToMany
     * @ORM\JoinTable(inverseJoinColumns={@ORM\JoinColumn(unique=true, onDelete="CASCADE")})
     */
    protected $accounts;

    /**
     * UserAccount constructor.
     * @param $facebookId
     * @param $email
     * @param $name
     * @param $avatar
     */
    public function __construct($facebookId, $email, $name, $avatar)
    {
        $this->facebookId = $facebookId;
        $this->name = $name;
        $this->avatar = $avatar;
        $this->email = $email;

        $this->accounts = new ArrayCollection();
    }

    /**
     * Assigns the given account to this party.
     *
     * @param Account $account The account
     * @return void
     */
    public function addAccount(Account $account)
    {
        $this->accounts->add($account);
    }

    /**
     * Remove an account from this party
     *
     * @param Account $account The account to remove
     * @return void
     */
    public function removeAccount(Account $account)
    {
        $this->accounts->removeElement($account);
    }

    /**
     * Returns the accounts of this party
     *
     * @return Collection<Account>|Account[] All assigned Neos\Flow\Security\Account objects
     */
    public function getAccounts()
    {
        return $this->accounts;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getFacebookId()
    {
        return $this->facebookId;
    }

    public function hasValidAccount()
    {
        /** @var Account $account */
        foreach ($this->accounts as $account) {
            if ($account->isActive()) {
                return true;
            }
        }

        return false;
    }
}
