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
    private $out;

    /**
     * @var \DateTime
     */
    private $in;

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
    private $seats_required;

    /**
     * @var string
     */
    private $destination_text;

    /**
     * @var string
     */
    private $notes;

    /**
     * @var boolean
     */
    private $is_noshow;

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
    public function setOut($out)
    {
        $this->out = $out;

        return $this;
    }

    /**
     * Get out
     *
     * @return \DateTime 
     */
    public function getOut()
    {
        return $this->out;
    }

    /**
     * Set in
     *
     * @param \DateTime $in
     * @return Reservation
     */
    public function setIn($in)
    {
        $this->in = $in;

        return $this;
    }

    /**
     * Get in
     *
     * @return \DateTime 
     */
    public function getIn()
    {
        return $this->in;
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
        $this->seats_required = $seatsRequired;

        return $this;
    }

    /**
     * Get seats_required
     *
     * @return integer 
     */
    public function getSeatsRequired()
    {
        return $this->seats_required;
    }

    /**
     * Set destination_text
     *
     * @param string $destinationText
     * @return Reservation
     */
    public function setDestinationText($destinationText)
    {
        $this->destination_text = $destinationText;

        return $this;
    }

    /**
     * Get destination_text
     *
     * @return string 
     */
    public function getDestinationText()
    {
        return $this->destination_text;
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
    public function setIsNoshow($isNoshow)
    {
        $this->is_noshow = $isNoshow;

        return $this;
    }

    /**
     * Get is_noshow
     *
     * @return boolean 
     */
    public function getIsNoshow()
    {
        return $this->is_noshow;
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
     * @var \DateTime
     */
    private $out_datetime;

    /**
     * @var \DateTime
     */
    private $in_datetime;


    /**
     * Set out_datetime
     *
     * @param \DateTime $outDatetime
     * @return Reservation
     */
    public function setOutDatetime($outDatetime)
    {
        $this->out_datetime = $outDatetime;

        return $this;
    }

    /**
     * Get out_datetime
     *
     * @return \DateTime 
     */
    public function getOutDatetime()
    {
        return $this->out_datetime;
    }

    /**
     * Set in_datetime
     *
     * @param \DateTime $inDatetime
     * @return Reservation
     */
    public function setInDatetime($inDatetime)
    {
        $this->in_datetime = $inDatetime;

        return $this;
    }

    /**
     * Get in_datetime
     *
     * @return \DateTime 
     */
    public function getInDatetime()
    {
        return $this->in_datetime;
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
     * @var \Ginsberg\TransportationBundle\Entity\Vehicle
     */
    private $vehicle;


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
     * @var \Ginsberg\TransportationBundle\Entity\Series
     */
    private $series;

    /**
     * @var \Ginsberg\TransportationBundle\Entity\Destination
     */
    private $destination;


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
     * @var \DateTime
     */
    private $checkout;

    /**
     * @var \DateTime
     */
    private $checkin;


    /**
     * Set checkout
     *
     * @param \DateTime $checkout
     * @return Reservation
     */
    public function setCheckout($checkout)
    {
        $this->checkout = $checkout;

        return $this;
    }

    /**
     * Get checkout
     *
     * @return \DateTime 
     */
    public function getCheckout()
    {
        return $this->checkout;
    }

    /**
     * Set checkin
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
     * Get checkin
     *
     * @return \DateTime 
     */
    public function getCheckin()
    {
        return $this->checkin;
    }
}
