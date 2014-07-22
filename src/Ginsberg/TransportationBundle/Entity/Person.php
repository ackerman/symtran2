<?php

namespace Ginsberg\TransportationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Person
 * 
 * @ORM\HasLifecycleCallbacks()
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
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

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
    private $dateApproved;

    /**
     * @var boolean
     */
    private $isTermsAgreed;

    /**
     * @var boolean
     */
    private $hasUnpaidTicket;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var \DateTime
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
    
    public $status_options = Array(
      'pending' =>'pending',
      'rejected'=>'rejected',
      'approved'=>'approved',
    );

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
     * Set firstName
     *
     * @param string $firstName
     * @return Person
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string 
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     * @return Person
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string 
     */
    public function getLastName()
    {
        return $this->lastName;
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
     * Set dateApproved
     *
     * @param \DateTime $dateApproved
     * @return Person
     */
    public function setDateApproved($dateApproved = null)
    {
        $this->dateApproved = $dateApproved;

        return $this;
    }

    /**
     * Get dateApproved
     *
     * @return \DateTime 
     */
    public function getDateApproved()
    {
        return $this->dateApproved;
    }

    /**
     * Set isTermsAgreed
     *
     * @param boolean $isTermsAgreed
     * @return Person
     */
    public function setIsTermsAgreed($isTermsAgreed)
    {
        $this->isTermsAgreed = $isTermsAgreed;

        return $this;
    }

    /**
     * Get isTermsAgreed
     *
     * @return boolean 
     */
    public function getIsTermsAgreed()
    {
        return $this->isTermsAgreed;
    }

    /**
     * Set hasUnpaidTicket
     *
     * @param boolean $hasUnpaidTicket
     * @return Person
     */
    public function setHasUnpaidTicket($hasUnpaidTicket)
    {
        $this->hasUnpaidTicket = $hasUnpaidTicket;

        return $this;
    }

    /**
     * Get hasUnpaidTicket
     *
     * @return boolean 
     */
    public function getHasUnpaidTicket()
    {
        return $this->hasUnpaidTicket;
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
     * @param \DateTime $modified
     * @return Person
     */
    public function setModified($modified = NULL)
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * Get modified
     *
     * @return \DateTime 
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
    public function setProgram(\Ginsberg\TransportationBundle\Entity\Program $program)
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
     * @ORM\PrePersist
     */
    public function setCreatedValue()
    {
      $this->setCreated(new \DateTime());
    }

    /**
     * @ORM\PreUpdate
     */
    public function setModifiedValue()
    {
      $this->setModified(new \DateTime());
    }
    
    public function findByStatus($status) {
      $dql = "SELECT p, prog FROM GinsbergTransportationBundle:Person p JOIN d.program prog WHERE p.status = :status ORDER BY p.created ASC";
        $query = getEntityManager()->createQuery($dql)->setParameter('status', $status);
        
        try {
          return $query->getResult();
        } catch (\Doctrine\ORM\NoResultException $ex) {
          return null;
        }
    }
    
    public function __toString() {
      return $this->uniqname;
    }
}
