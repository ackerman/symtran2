<?php

namespace Ginsberg\TransportationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Person
 * 
 * @ORM\Entity(repositoryClass="Ginsberg\TransportationBundle\Entity\PersonRepository")
 */
class Person
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $first_name;

    /**
     * @var string
     */
    private $last_name;

    /**
     * @var string
     */
    private $uniqname;

    /**
     * @var string
     */
    private $phone;

    /**
     * @var string
     */
    private $status;

    /**
     * @var \DateTime
     */
    private $date_approved;

    /**
     * @var boolean
     */
    private $is_terms_agreed;

    /**
     * @var boolean
     */
    private $is_ticket_unpaid;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var datatime
     */
    private $modified;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $reservations;

    /**
     * @var \Ginsberg\TransportationBundle\Entity\Program
     */
    private $program;

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
     * Set first_name
     *
     * @param string $firstName
     * @return Person
     */
    public function setFirstName($firstName)
    {
        $this->first_name = $firstName;

        return $this;
    }

    /**
     * Get first_name
     *
     * @return string 
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Set last_name
     *
     * @param string $lastName
     * @return Person
     */
    public function setLastName($lastName)
    {
        $this->last_name = $lastName;

        return $this;
    }

    /**
     * Get last_name
     *
     * @return string 
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * Set uniqname
     *
     * @param string $uniqname
     * @return Person
     */
    public function setUniqname($uniqname)
    {
        $this->uniqname = $uniqname;

        return $this;
    }

    /**
     * Get uniqname
     *
     * @return string 
     */
    public function getUniqname()
    {
        return $this->uniqname;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return Person
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Person
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set date_approved
     *
     * @param \DateTime $dateApproved
     * @return Person
     */
    public function setDateApproved($dateApproved)
    {
        $this->date_approved = $dateApproved;

        return $this;
    }

    /**
     * Get date_approved
     *
     * @return \DateTime 
     */
    public function getDateApproved()
    {
        return $this->date_approved;
    }

    /**
     * Set is_terms_agreed
     *
     * @param boolean $isTermsAgreed
     * @return Person
     */
    public function setIsTermsAgreed($isTermsAgreed)
    {
        $this->is_terms_agreed = $isTermsAgreed;

        return $this;
    }

    /**
     * Get is_terms_agreed
     *
     * @return boolean 
     */
    public function getIsTermsAgreed()
    {
        return $this->is_terms_agreed;
    }

    /**
     * Set is_ticket_unpaid
     *
     * @param boolean $isTicketUnpaid
     * @return Person
     */
    public function setIsTicketUnpaid($isTicketUnpaid)
    {
        $this->is_ticket_unpaid = $isTicketUnpaid;

        return $this;
    }

    /**
     * Get is_ticket_unpaid
     *
     * @return boolean 
     */
    public function getIsTicketUnpaid()
    {
        return $this->is_ticket_unpaid;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Person
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set modified
     *
     * @param \datatime $modified
     * @return Person
     */
    public function setModified(\datatime $modified)
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * Get modified
     *
     * @return \datatime 
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Add reservations
     *
     * @param \Ginsberg\TransportationBundle\Entity\Reservation $reservations
     * @return Person
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
     * Set program
     *
     * @param \Ginsberg\TransportationBundle\Entity\Program $program
     * @return Person
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
    
    /**
     * To String
     * 
     * @return string First and Last Names
     */
    public function __toString() {
      if ($this->getFirstName() && $this->getLastName()) {
        return $this->getFirstName() . " " . $this->getLastName();
      }
      else 
      {
        return "";
      }
    }
}
