<?php

namespace Ginsberg\TransportationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Destination
 */
class Destination
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
    private $is_active;

    /**
     * @var \Ginsberg\TransportationBundle\Entity\Program
     * @ORM\ManyToOne(targetEntity="Program", inversedBy="destinations")
     * @ORM\JoinColumn(name="program_id", referencedColumnName="id")
     */
    private $program;


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
     * @return Destination
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
     * Set is_active
     *
     * @param boolean $isActive
     * @return Destination
     */
    public function setIsActive($isActive)
    {
        $this->is_active = $isActive;

        return $this;
    }

    /**
     * Get is_active
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->is_active;
    }

    /**
     * Set program
     *
     * @param \Ginsberg\TransportationBundle\Entity\Program $program
     * @return Destination
     */
    public function setProgram(\Ginsberg\TransportationBundle\Entity\Program $program = null)
    {
        $this->program = $program;

        return $this;
    }

    /**
     * Get program
     *
     * @return \Ginsberg\TransportationBundle\Entity\Program 
     */
    public function getProgram()
    {
        return $this->program;
    }
}
