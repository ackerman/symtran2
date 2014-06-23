<?php

namespace Ginsberg\TransportationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Program
 * 
 * @ORM\Entity
 * @ORM\Table(name="program")
 */
class Program
{
    /**
     * @var integer
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @var integer
     * @ORM\Column(type="integer")
     */
    private $shortcode;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $eligibility_group;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\OneToMany(targetEntity="Destination", mappedBy="program")
     */
    private $destinations;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\OneToMany(targetEntity="Reservation", mappedBy="program")
     */
    private $reservations;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\OneToMany(targetEntity="Vehicle", mappedBy="program")
     */
    private $vehicles;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\OneToMany(targetEntity="Person", mappedBy="program")
     */
    private $persons;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->destinations = new ArrayCollection();
        $this->reservations = new ArrayCollection();
        $this->vehicles = new ArrayCollection();
        $this->persons = new ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return Program
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
     * Set shortcode
     *
     * @param integer $shortcode
     * @return Program
     */
    public function setShortcode($shortcode)
    {
        $this->shortcode = $shortcode;

        return $this;
    }

    /**
     * Get shortcode
     *
     * @return integer 
     */
    public function getShortcode()
    {
        return $this->shortcode;
    }

    /**
     * Set eligibility_group
     *
     * @param string $eligibilityGroup
     * @return Program
     */
    public function setEligibilityGroup($eligibilityGroup)
    {
        $this->eligibility_group = $eligibilityGroup;

        return $this;
    }

    /**
     * Get eligibility_group
     *
     * @return string 
     */
    public function getEligibilityGroup()
    {
        return $this->eligibility_group;
    }

    /**
     * Add destinations
     *
     * @param \Ginsberg\TransportationBundle\Entity\Destination $destinations
     * @return Program
     */
    public function addDestination(\Ginsberg\TransportationBundle\Entity\Destination $destinations)
    {
        $this->destinations[] = $destinations;

        return $this;
    }

    /**
     * Remove destinations
     *
     * @param \Ginsberg\TransportationBundle\Entity\Destination $destinations
     */
    public function removeDestination(\Ginsberg\TransportationBundle\Entity\Destination $destinations)
    {
        $this->destinations->removeElement($destinations);
    }

    /**
     * Get destinations
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDestinations()
    {
        return $this->destinations;
    }
    
    public function __toString() {
      return $this->getName();
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $persons;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $vehicles;


    /**
     * Add persons
     *
     * @param \Ginsberg\TransportationBundle\Entity\Person $persons
     * @return Program
     */
    public function addPerson(\Ginsberg\TransportationBundle\Entity\Person $persons)
    {
        $this->persons[] = $persons;

        return $this;
    }

    /**
     * Remove persons
     *
     * @param \Ginsberg\TransportationBundle\Entity\Person $persons
     */
    public function removePerson(\Ginsberg\TransportationBundle\Entity\Person $persons)
    {
        $this->persons->removeElement($persons);
    }

    /**
     * Get persons
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPersons()
    {
        return $this->persons;
    }

    /**
     * Add vehicles
     *
     * @param \Ginsberg\TransportationBundle\Entity\Vehicle $vehicles
     * @return Program
     */
    public function addVehicle(\Ginsberg\TransportationBundle\Entity\Vehicle $vehicles)
    {
        $this->vehicles[] = $vehicles;

        return $this;
    }

    /**
     * Remove vehicles
     *
     * @param \Ginsberg\TransportationBundle\Entity\Vehicle $vehicles
     */
    public function removeVehicle(\Ginsberg\TransportationBundle\Entity\Vehicle $vehicles)
    {
        $this->vehicles->removeElement($vehicles);
    }

    /**
     * Get vehicles
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getVehicles()
    {
        return $this->vehicles;
    }

    /**
     * Add reservations
     *
     * @param \Ginsberg\TransportationBundle\Entity\Reservation $reservations
     * @return Program
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
