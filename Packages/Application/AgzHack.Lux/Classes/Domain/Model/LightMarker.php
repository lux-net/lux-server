<?php

namespace AgzHack\Lux\Domain\Model;

use AgzHack\Auth\Domain\Model\UserAccount;
use AgzHack\Geo\Domain\Model\Coordinate;
use Doctrine\Common\Collections\ArrayCollection;
use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 */
class LightMarker
{

    /**
     * @var \Doctrine\Common\Collections\Collection<\AgzHack\Lux\Domain\Model\LightMarker>
     * @ORM\OneToMany(mappedBy="parentMarker",cascade={"persist"})
     */
    protected $subMarkers;

    /**
     * @var \AgzHack\Lux\Domain\Model\LightMarker
     * @ORM\ManyToOne(inversedBy="subMarkers")
     */
    protected $parentMarker;

    /**
     * @var Coordinate
     */
    protected $coordinate;

    /**
     * @var boolean
     */
    protected $iluminated;

    /**
     * @var \DateTime
     * @ORM\Column(nullable=true)
     */
    protected $confirmedAt;

    /**
     * @var UserAccount
     * @ORM\ManyToOne
     */
    protected $userAccount;

    /**
     * LightMarker constructor.
     * @param Coordinate $coordinate
     * @param bool $iluminated
     * @param \DateTime $confirmedAt
     */
    public function __construct(Coordinate $coordinate, $iluminated = true, \DateTime $confirmedAt = null)
    {
        $this->coordinate = $coordinate;
        $this->iluminated = $iluminated;
        $this->confirmedAt = $confirmedAt;
    }


    /**
     * @param LightMarker $lightMarker
     */
    public function addSubMarker(LightMarker $lightMarker)
    {
        $lightMarker->setParentMarker($this);
        $this->subMarkers->add($lightMarker);

        $this->iluminated = $lightMarker->isIluminated();
        $this->confirmedAt = null;
    }

    /**
     * @return bool
     */
    public function isIluminated()
    {
        return $this->iluminated;
    }

    /**
     * @return \DateTime
     */
    public function getConfirmedAt()
    {
        return $this->confirmedAt;
    }

    public function confirm()
    {
        $this->confirmedAt = new \DateTime('now');
    }


    public function toggle()
    {
        $this->iluminated = !$this->iluminated;
        $this->confirmedAt = null;
    }

    /**
     * @param LightMarker $parentMarker
     */
    public function setParentMarker($parentMarker)
    {
        $this->parentMarker = $parentMarker;
    }

    /**
     * @return Coordinate
     */
    public function getCoordinate()
    {
        return $this->coordinate;
    }

    /**
     * @return UserAccount
     */
    public function getUserAccount()
    {
        return $this->userAccount;
    }

    /**
     * @param UserAccount $userAccount
     */
    public function setUserAccount($userAccount)
    {
        $this->userAccount = $userAccount;
    }
}
