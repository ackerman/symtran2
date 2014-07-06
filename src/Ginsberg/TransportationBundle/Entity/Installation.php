<?php

namespace Ginsberg\TransportationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Installation
 * 
 * @ORM\Entity(repositoryClass="Ginsberg\TransportationBundle\Entity\InstallationRepository")
 */
class Installation
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var boolean
     */
    private $isOpen;

    /**
     * @var string
     */
    private $reservationsOpen;

    /**
     * @var string
     */
    private $carsAvailable;

    /**
     * @var \DateTime
     */
    private $fallStart;

    /**
     * @var \DateTime
     */
    private $thanksgivingStart;

    /**
     * @var \DateTime
     */
    private $thanksgivingEnd;

    /**
     * @var \DateTime
     */
    private $fallEnd;

    /**
     * @var \DateTime
     */
    private $winterStart;

    /**
     * @var \DateTime
     */
    private $mlkStart;

    /**
     * @var \DateTime
     */
    private $mlkEnd;

    /**
     * @var \DateTime
     */
    private $springbreakStart;

    /**
     * @var \DateTime
     */
    private $springbreakEnd;

    /**
     * @var \DateTime
     */
    private $winterEnd;

    /**
     * @var \DateTime
     */
    private $fallBack;

    /**
     * @var \DateTime
     */
    private $springForward;

    /**
     * @var string
     */
    private $dailyOpen;

    /**
     * @var string
     */
    private $dailyClose;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Installation
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set open
     *
     * @param boolean $open
     * @return Installation
     */
    public function setIsOpen($isOpen)
    {
        $this->isOpen = $isOpen;

        return $this;
    }

    /**
     * Get open
     *
     * @return boolean 
     */
    public function getIsOpen()
    {
        return $this->isOpen;
    }

    /**
     * Set reservationsOpen
     *
     * @param string $reservationsOpen
     * @return Installation
     */
    public function setReservationsOpen($reservationsOpen)
    {
        $this->reservationsOpen = $reservationsOpen;

        return $this;
    }

    /**
     * Get reservationsOpen
     *
     * @return string 
     */
    public function getReservationsOpen()
    {
        return $this->reservationsOpen;
    }

    /**
     * Set carsAvailable
     *
     * @param string $carsAvailable
     * @return Installation
     */
    public function setCarsAvailable($carsAvailable)
    {
        $this->carsAvailable = $carsAvailable;

        return $this;
    }

    /**
     * Get carsAvailable
     *
     * @return string 
     */
    public function getCarsAvailable()
    {
        return $this->carsAvailable;
    }

    /**
     * Set fallStart
     *
     * @param \DateTime $fallStart
     * @return Installation
     */
    public function setFallStart($fallStart)
    {
        $this->fallStart = $fallStart;

        return $this;
    }

    /**
     * Get fallStart
     *
     * @return \DateTime 
     */
    public function getFallStart()
    {
        return $this->fallStart;
    }

    /**
     * Set thanksgivingStart
     *
     * @param \DateTime $thanksgivingStart
     * @return Installation
     */
    public function setThanksgivingStart($thanksgivingStart)
    {
        $this->thanksgivingStart = $thanksgivingStart;

        return $this;
    }

    /**
     * Get thanksgivingStart
     *
     * @return \DateTime 
     */
    public function getThanksgivingStart()
    {
        return $this->thanksgivingStart;
    }

    /**
     * Set thanksgivingEnd
     *
     * @param \DateTime $thanksgivingEnd
     * @return Installation
     */
    public function setThanksgivingEnd($thanksgivingEnd)
    {
        $this->thanksgivingEnd = $thanksgivingEnd;

        return $this;
    }

    /**
     * Get thanksgivingEnd
     *
     * @return \DateTime 
     */
    public function getThanksgivingEnd()
    {
        return $this->thanksgivingEnd;
    }

    /**
     * Set fallEnd
     *
     * @param \DateTime $fallEnd
     * @return Installation
     */
    public function setFallEnd($fallEnd)
    {
        $this->fallEnd = $fallEnd;

        return $this;
    }

    /**
     * Get fallEnd
     *
     * @return \DateTime 
     */
    public function getFallEnd()
    {
        return $this->fallEnd;
    }

    /**
     * Set winterStart
     *
     * @param \DateTime $winterStart
     * @return Installation
     */
    public function setWinterStart($winterStart)
    {
        $this->winterStart = $winterStart;

        return $this;
    }

    /**
     * Get winterStart
     *
     * @return \DateTime 
     */
    public function getWinterStart()
    {
        return $this->winterStart;
    }

    /**
     * Set mlkStart
     *
     * @param \DateTime $mlkStart
     * @return Installation
     */
    public function setMlkStart($mlkStart)
    {
        $this->mlkStart = $mlkStart;

        return $this;
    }

    /**
     * Get mlkStart
     *
     * @return \DateTime 
     */
    public function getMlkStart()
    {
        return $this->mlkStart;
    }

    /**
     * Set mlkEnd
     *
     * @param \DateTime $mlkEnd
     * @return Installation
     */
    public function setMlkEnd($mlkEnd)
    {
        $this->mlkEnd = $mlkEnd;

        return $this;
    }

    /**
     * Get mlkEnd
     *
     * @return \DateTime 
     */
    public function getMlkEnd()
    {
        return $this->mlkEnd;
    }

    /**
     * Set springbreakStart
     *
     * @param \DateTime $springbreakStart
     * @return Installation
     */
    public function setSpringbreakStart($springbreakStart)
    {
        $this->springbreakStart = $springbreakStart;

        return $this;
    }

    /**
     * Get springbreakStart
     *
     * @return \DateTime 
     */
    public function getSpringbreakStart()
    {
        return $this->springbreakStart;
    }

    /**
     * Set springbreakEnd
     *
     * @param \DateTime $springbreakEnd
     * @return Installation
     */
    public function setSpringbreakEnd($springbreakEnd)
    {
        $this->springbreakEnd = $springbreakEnd;

        return $this;
    }

    /**
     * Get springbreakEnd
     *
     * @return \DateTime 
     */
    public function getSpringbreakEnd()
    {
        return $this->springbreakEnd;
    }

    /**
     * Set winterEnd
     *
     * @param \DateTime $winterEnd
     * @return Installation
     */
    public function setWinterEnd($winterEnd)
    {
        $this->winterEnd = $winterEnd;

        return $this;
    }

    /**
     * Get winterEnd
     *
     * @return \DateTime 
     */
    public function getWinterEnd()
    {
        return $this->winterEnd;
    }

    /**
     * Set fallBack
     *
     * @param \DateTime $fallBack
     * @return Installation
     */
    public function setFallBack($fallBack)
    {
        $this->fallBack = $fallBack;

        return $this;
    }

    /**
     * Get fallBack
     *
     * @return \DateTime 
     */
    public function getFallBack()
    {
        return $this->fallBack;
    }

    /**
     * Set springForward
     *
     * @param \DateTime $springForward
     * @return Installation
     */
    public function setSpringForward($springForward)
    {
        $this->springForward = $springForward;

        return $this;
    }

    /**
     * Get springForward
     *
     * @return \DateTime 
     */
    public function getSpringForward()
    {
        return $this->springForward;
    }
    
    /**
     * Set dailyOpen
     *
     * @param string $dailyOpen
     * @return Installation
     */
    public function setDailyOpen($dailyOpen)
    {
        $this->dailyOpen = $dailyOpen;

        return $this;
    }

    /**
     * Get dailyOpen
     *
     * @return string 
     */
    public function getDailyOpen()
    {
        return $this->dailyOpen;
    }

    /**
     * Set dailyClose
     *
     * @param string $dailyClose
     * @return Installation
     */
    public function setDailyClose($dailyClose)
    {
        $this->dailyClose = $dailyClose;

        return $this;
    }

    /**
     * Get dailyClose
     *
     * @return string 
     */
    public function getDailyClose()
    {
        return $this->dailyClose;
    }
}
