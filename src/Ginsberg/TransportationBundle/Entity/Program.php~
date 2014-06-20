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
     * Constructor
     */
    public function __construct()
    {
        $this->destinations = new ArrayCollection();
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
}
