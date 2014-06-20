<?php

namespace Ginsberg\TransportationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Reservation
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
    public function setStart($start)
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
     */
    public function setModified($modified)
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
}
