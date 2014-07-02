<?php

namespace Ginsberg\TransportationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Program
 * 
 * @ORM\Entity(repositoryClass="Ginsberg\TransportationBundle\Entity\ProgramRepository")
 */
class Program
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
     * @var integer
     */
    private $shortcode;

    /**
     * @var string
     */
    private $eligibilityGroup;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $destinations;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $persons;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $vehicles;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $reservations;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->destinations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->persons = new \Doctrine\Common\Collections\ArrayCollection();
        $this->vehicles = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set eligibilityGroup
     *
     * @param string $eligibilityGroup
     * @return Program
     */
    public function setEligibilityGroup($eligibilityGroup)
    {
        $this->eligibilityGroup = $eligibilityGroup;

        return $this;
    }

    /**
     * Get eligibilityGroup
     *
     * @return string 
     */
    public function getEligibilityGroup()
    {
        return $this->eligibilityGroup;
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
    
    /**
     * To string
     * 
     * @return string The Program name
     */
    public function __toString() {
      return $this->getName();
    }
    
    /**
    * Get Program name based on official name of the program's MCommunity eligibility group.
    */
    public static function get_program_name_by_ldap_group($ldap_group) 
    {
      $repository = $this->getDoctrine()->getRepository('GinsbergTransportationBundle:Program');
      $program = $repository->findBy($ldap_group);
      if ($program) {
        return $program->getName();
      } else {
        return false;
      }
    }

  /**
   * Get Program Id based on official name of the program's MCommunity eligibility group.
   */
  public static function get_program_id_by_ldap_group($ldap_group) {
    $repository = $this->getDoctrine()->getRepository('GinsbergTransportationBundle:Program');
    $program = $repository->findBy($ldap_group);
    if ($program) {
      return $program->getId();
    } else {
      return false;
    }
  }
}
