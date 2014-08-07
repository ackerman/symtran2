<?php

namespace Ginsberg\TransportationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Reservation
 * 
 * @ORM\Entity(repositoryClass="Ginsberg\TransportationBundle\Entity\ReservationRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Reservation
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $start;

    /**
     * @var \DateTime
     */
    private $end;

    /**
     * @var \DateTime
     */
    private $checkout;

    /**
     * @var \DateTime
     */
    private $checkin;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var \DateTime
     */
    private $modified;

    /**
     * @var integer
     */
    private $seatsRequired;

    /**
     * @var string
     */
    private $destinationText;

    /**
     * @var string
     */
    private $notes;

    /**
     * @var boolean
     */
    private $isNoShow;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $tickets;

    /**
     * @var \Ginsberg\TransportationBundle\Entity\Person
     */
    private $person;
    
    /**
     * @var \Ginsberg\TransportationBundle\Entity\Program
     * @ORM\ManyToOne(targetEntity="Program", inversedBy="reservations")
     * @ORM\JoinColumn(name="program_id", referencedColumnName="id", nullable=false)
     */
    private $program;
    
    /**
     * @var \Ginsberg\TransportationBundle\Entity\Series
     */
    private $series;

    /**
     * @var \Ginsberg\TransportationBundle\Entity\Destination
     */
    private $destination;

    /**
     * @var \Ginsberg\TransportationBundle\Entity\Vehicle
     */
    private $vehicle;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tickets = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set start
     *
     * @param \DateTime $start
     * @return Reservation
     */
    public function setStart($start = null)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * Get start
     *
     * @return \DateTime 
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set end
     *
     * @param \DateTime $end
     * @return Reservation
     */
    public function setEnd($end)
    {
        $this->end = $end;

        return $this;
    }

    /**
     * Get end
     *
     * @return \DateTime 
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Set out
     *
     * @param \DateTime $out
     * @return Reservation
     */
    public function setCheckout($checkout)
    {
        $this->checkout = $checkout;

        return $this;
    }

    /**
     * Get out
     *
     * @return \DateTime 
     */
    public function getCheckout()
    {
        return $this->checkout;
    }

    /**
     * Set in
     *
     * @param \DateTime $checkin
     * @return Reservation
     */
    public function setCheckin($checkin)
    {
        $this->checkin = $checkin;

        return $this;
    }

    /**
     * Get in
     *
     * @return \DateTime 
     */
    public function getCheckin()
    {
        return $this->checkin;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Reservation
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
     * @return Reservation
     * @ORM\PreUpdate
     */
    public function setModified($modified = null)
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
     * Set seats_required
     *
     * @param integer $seatsRequired
     * @return Reservation
     */
    public function setSeatsRequired($seatsRequired)
    {
        $this->seatsRequired = $seatsRequired;

        return $this;
    }

    /**
     * Get seats_required
     *
     * @return integer 
     */
    public function getSeatsRequired()
    {
        return $this->seatsRequired;
    }

    /**
     * Set destination_text
     *
     * @param string $destinationText
     * @return Reservation
     */
    public function setDestinationText($destinationText)
    {
        $this->destinationText = $destinationText;

        return $this;
    }

    /**
     * Get destination_text
     *
     * @return string 
     */
    public function getDestinationText()
    {
        return $this->destinationText;
    }

    /**
     * Set notes
     *
     * @param string $notes
     * @return Reservation
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get notes
     *
     * @return string 
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set is_noshow
     *
     * @param boolean $isNoshow
     * @return Reservation
     */
    public function setIsNoShow($isNoShow)
    {
        $this->isNoShow = $isNoShow;

        return $this;
    }

    /**
     * Get is_noshow
     *
     * @return boolean 
     */
    public function getIsNoShow()
    {
        return $this->isNoShow;
    }

    /**
     * Add tickets
     *
     * @param \Ginsberg\TransportationBundle\Entity\Ticket $tickets
     * @return Reservation
     */
    public function addTicket(\Ginsberg\TransportationBundle\Entity\Ticket $tickets)
    {
        $this->tickets[] = $tickets;

        return $this;
    }

    /**
     * Remove tickets
     *
     * @param \Ginsberg\TransportationBundle\Entity\Ticket $tickets
     */
    public function removeTicket(\Ginsberg\TransportationBundle\Entity\Ticket $tickets)
    {
        $this->tickets->removeElement($tickets);
    }

    /**
     * Get tickets
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTickets()
    {
        return $this->tickets;
    }

    /**
     * Set person
     *
     * @param \Ginsberg\TransportationBundle\Entity\Person $person
     * @return Reservation
     */
    public function setPerson(\Ginsberg\TransportationBundle\Entity\Person $person = null)
    {
        $this->person = $person;

        return $this;
    }

    /**
     * Get person
     *
     * @return \Ginsberg\TransportationBundle\Entity\Person 
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * Set program
     *
     * @param \Ginsberg\TransportationBundle\Entity\Program $program
     * @return Reservation
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
     * Set vehicle
     *
     * @param \Ginsberg\TransportationBundle\Entity\Vehicle $vehicle
     * @return Reservation
     */
    public function setVehicle(\Ginsberg\TransportationBundle\Entity\Vehicle $vehicle = null)
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    /**
     * Get vehicle
     *
     * @return \Ginsberg\TransportationBundle\Entity\Vehicle 
     */
    public function getVehicle()
    {
        return $this->vehicle;
    }
    

    /**
     * Set series
     *
     * @param \Ginsberg\TransportationBundle\Entity\Series $series
     * @return Reservation
     */
    public function setSeries(\Ginsberg\TransportationBundle\Entity\Series $series = null)
    {
        $this->series = $series;

        return $this;
    }

    /**
     * Get series
     *
     * @return \Ginsberg\TransportationBundle\Entity\Series 
     */
    public function getSeries()
    {
        return $this->series;
    }

    /**
     * Set destination
     *
     * @param \Ginsberg\TransportationBundle\Entity\Destination $destination
     * @return Reservation
     */
    public function setDestination(\Ginsberg\TransportationBundle\Entity\Destination $destination = null)
    {
        $this->destination = $destination;

        return $this;
    }

    /**
     * Get destination
     *
     * @return \Ginsberg\TransportationBundle\Entity\Destination 
     */
    public function getDestination()
    {
        return $this->destination;
    }
    
    /**
     * Return ID as a string
     * 
     * @return string
     */
    public function __toString() {
      return (string) $this->getId();
    }
    
  /**
   * Returns an array representation of a Reservation.
   * 
   * @return array
   */
  public function toArray() {
    $reservationArray = array();
    $reservationArray[] = $this->getStart()->format('Y-m-d');
    $reservationArray[] = $this->getEnd()->format('Y-m-d');
    $reservationArray[] = $this->getProgram()->getName();
    $reservationArray[] = $this->getProgram()->getShortcode();
    $reservationArray[] = $this->getPerson()->getUniqname();
    $reservationArray[] = $this->getVehicle()->getType() . ' ' . $this->getVehicle()->getName();
    ($this->getProgram()->getName() == "Project Community") ? $this->getDestination() : $this->getDestinationText();
    $reservationArray[] = $this->getIsNoShow();
    $ticketString = '';
    foreach($this->getTickets() as $ticket) {
      if ($ticket->getIsPaid()) {
        $ticketString .= 'Id: ' . $ticket->getId() . ', $' . $ticket->getAmount() . ' ';
      } else {
        $ticketString .= 'Id: ' . $ticket->getId() . ', $' . $ticket->getAmount() . ' (unpaid) '; 
      }
    }
    $reservationArray[] = $ticketString;
    $reservationArray[] = $this->getId();
    $reservationArray[] = $this->getNotes();
    
    return $reservationArray;
  }
}
