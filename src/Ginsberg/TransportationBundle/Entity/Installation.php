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
    private $open;

    /**
     * @var string
     */
    private $reservations_open;

    /**
     * @var string
     */
    private $cars_available;

    /**
     * @var \DateTime
     */
    private $fall_start;

    /**
     * @var \DateTime
     */
    private $thanksgiving_start;

    /**
     * @var \DateTime
     */
    private $thanksgiving_end;

    /**
     * @var \DateTime
     */
    private $fall_end;

    /**
     * @var \DateTime
     */
    private $winter_start;

    /**
     * @var \DateTime
     */
    private $mlk_start;

    /**
     * @var \DateTime
     */
    private $mlk_end;

    /**
     * @var \DateTime
     */
    private $springbreak_start;

    /**
     * @var \DateTime
     */
    private $springbreak_end;

    /**
     * @var \DateTime
     */
    private $winter_end;

    /**
     * @var \DateTime
     */
    private $fall_back;

    /**
     * @var \DateTime
     */
    private $spring_forward;


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
    public function setOpen($open)
    {
        $this->open = $open;

        return $this;
    }

    /**
     * Get open
     *
     * @return boolean 
     */
    public function getOpen()
    {
        return $this->open;
    }

    /**
     * Set reservations_open
     *
     * @param string $reservationsOpen
     * @return Installation
     */
    public function setReservationsOpen($reservationsOpen)
    {
        $this->reservations_open = $reservationsOpen;

        return $this;
    }

    /**
     * Get reservations_open
     *
     * @return string 
     */
    public function getReservationsOpen()
    {
        return $this->reservations_open;
    }

    /**
     * Set cars_available
     *
     * @param string $carsAvailable
     * @return Installation
     */
    public function setCarsAvailable($carsAvailable)
    {
        $this->cars_available = $carsAvailable;

        return $this;
    }

    /**
     * Get cars_available
     *
     * @return string 
     */
    public function getCarsAvailable()
    {
        return $this->cars_available;
    }

    /**
     * Set fall_start
     *
     * @param \DateTime $fallStart
     * @return Installation
     */
    public function setFallStart($fallStart)
    {
        $this->fall_start = $fallStart;

        return $this;
    }

    /**
     * Get fall_start
     *
     * @return \DateTime 
     */
    public function getFallStart()
    {
        return $this->fall_start;
    }

    /**
     * Set thanksgiving_start
     *
     * @param \DateTime $thanksgivingStart
     * @return Installation
     */
    public function setThanksgivingStart($thanksgivingStart)
    {
        $this->thanksgiving_start = $thanksgivingStart;

        return $this;
    }

    /**
     * Get thanksgiving_start
     *
     * @return \DateTime 
     */
    public function getThanksgivingStart()
    {
        return $this->thanksgiving_start;
    }

    /**
     * Set thanksgiving_end
     *
     * @param \DateTime $thanksgivingEnd
     * @return Installation
     */
    public function setThanksgivingEnd($thanksgivingEnd)
    {
        $this->thanksgiving_end = $thanksgivingEnd;

        return $this;
    }

    /**
     * Get thanksgiving_end
     *
     * @return \DateTime 
     */
    public function getThanksgivingEnd()
    {
        return $this->thanksgiving_end;
    }

    /**
     * Set fall_end
     *
     * @param \DateTime $fallEnd
     * @return Installation
     */
    public function setFallEnd($fallEnd)
    {
        $this->fall_end = $fallEnd;

        return $this;
    }

    /**
     * Get fall_end
     *
     * @return \DateTime 
     */
    public function getFallEnd()
    {
        return $this->fall_end;
    }

    /**
     * Set winter_start
     *
     * @param \DateTime $winterStart
     * @return Installation
     */
    public function setWinterStart($winterStart)
    {
        $this->winter_start = $winterStart;

        return $this;
    }

    /**
     * Get winter_start
     *
     * @return \DateTime 
     */
    public function getWinterStart()
    {
        return $this->winter_start;
    }

    /**
     * Set mlk_start
     *
     * @param \DateTime $mlkStart
     * @return Installation
     */
    public function setMlkStart($mlkStart)
    {
        $this->mlk_start = $mlkStart;

        return $this;
    }

    /**
     * Get mlk_start
     *
     * @return \DateTime 
     */
    public function getMlkStart()
    {
        return $this->mlk_start;
    }

    /**
     * Set mlk_end
     *
     * @param \DateTime $mlkEnd
     * @return Installation
     */
    public function setMlkEnd($mlkEnd)
    {
        $this->mlk_end = $mlkEnd;

        return $this;
    }

    /**
     * Get mlk_end
     *
     * @return \DateTime 
     */
    public function getMlkEnd()
    {
        return $this->mlk_end;
    }

    /**
     * Set springbreak_start
     *
     * @param \DateTime $springbreakStart
     * @return Installation
     */
    public function setSpringbreakStart($springbreakStart)
    {
        $this->springbreak_start = $springbreakStart;

        return $this;
    }

    /**
     * Get springbreak_start
     *
     * @return \DateTime 
     */
    public function getSpringbreakStart()
    {
        return $this->springbreak_start;
    }

    /**
     * Set springbreak_end
     *
     * @param \DateTime $springbreakEnd
     * @return Installation
     */
    public function setSpringbreakEnd($springbreakEnd)
    {
        $this->springbreak_end = $springbreakEnd;

        return $this;
    }

    /**
     * Get springbreak_end
     *
     * @return \DateTime 
     */
    public function getSpringbreakEnd()
    {
        return $this->springbreak_end;
    }

    /**
     * Set winter_end
     *
     * @param \DateTime $winterEnd
     * @return Installation
     */
    public function setWinterEnd($winterEnd)
    {
        $this->winter_end = $winterEnd;

        return $this;
    }

    /**
     * Get winter_end
     *
     * @return \DateTime 
     */
    public function getWinterEnd()
    {
        return $this->winter_end;
    }

    /**
     * Set fall_back
     *
     * @param \DateTime $fallBack
     * @return Installation
     */
    public function setFallBack($fallBack)
    {
        $this->fall_back = $fallBack;

        return $this;
    }

    /**
     * Get fall_back
     *
     * @return \DateTime 
     */
    public function getFallBack()
    {
        return $this->fall_back;
    }

    /**
     * Set spring_forward
     *
     * @param \DateTime $springForward
     * @return Installation
     */
    public function setSpringForward($springForward)
    {
        $this->spring_forward = $springForward;

        return $this;
    }

    /**
     * Get spring_forward
     *
     * @return \DateTime 
     */
    public function getSpringForward()
    {
        return $this->spring_forward;
    }
}
