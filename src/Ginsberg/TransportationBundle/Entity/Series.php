<?php

namespace Ginsberg\TransportationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Series
 */
class Series
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $reservations;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->reservations = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Add reservations
     *
     * @param \Ginsberg\TransportationBundle\Entity\Reservation $reservations
     * @return Series
     */
    public function addReservation(\Ginsberg\TransportationBundle\Entity\Reservation $reservations)
    {
        $this->reservations[] = $reservations;

        return $this;
    }

    /**
     * Remove reservations
     *
     * @param \Ginsberg\TransportationBundle\Entity\Reservation $reservations
     */
    public function removeReservation(\Ginsberg\TransportationBundle\Entity\Reservation $reservations)
    {
        $this->reservations->removeElement($reservations);
    }

    /**
     * Get reservations
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getReservations()
    {
        return $this->reservations;
    }
}
