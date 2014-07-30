<?php

namespace Ginsberg\TransportationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ticket
 * 
 * @ORM\Entity(repositoryClass="Ginsberg\TransportationBundle\Entity\TicketRepository")
 */
class Ticket
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $ticketDate;

    /**
     * @var string
     */
    private $reason;

    /**
     * @var string
     */
    private $location;

    /**
     * @var string
     */
    private $amount;

    /**
     * @var boolean
     */
    private $isPaid;

    /**
     * @var \Ginsberg\TransportationBundle\Entity\Reservation
     */
    private $reservation;


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
     * Set ticketDate
     *
     * @param \DateTime $ticketDate
     * @return Ticket
     */
    public function setTicketDate($ticketDate)
    {
        $this->ticketDate = $ticketDate;

        return $this;
    }

    /**
     * Get ticketDate
     *
     * @return \DateTime 
     */
    public function getTicketDate()
    {
        return $this->ticketDate;
    }

    /**
     * Set reason
     *
     * @param string $reason
     * @return Ticket
     */
    public function setReason($reason)
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * Get reason
     *
     * @return string 
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Set location
     *
     * @param string $location
     * @return Ticket
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return string 
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set amount
     *
     * @param string $amount
     * @return Ticket
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return string 
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set isPaid
     *
     * @param boolean $isPaid
     * @return Ticket
     */
    public function setIsPaid($isPaid)
    {
        $this->isPaid = $isPaid;

        return $this;
    }

    /**
     * Get isPaid
     *
     * @return boolean 
     */
    public function getIsPaid()
    {
        return $this->isPaid;
    }

    /**
     * Set reservation
     *
     * @param \Ginsberg\TransportationBundle\Entity\Reservation $reservation
     * @return Ticket
     */
    public function setReservation(\Ginsberg\TransportationBundle\Entity\Reservation $reservation = null)
    {
        $this->reservation = $reservation;

        return $this;
    }

    /**
     * Get reservation
     *
     * @return \Ginsberg\TransportationBundle\Entity\Reservation 
     */
    public function getReservation()
    {
        return $this->reservation;
    }
    

}
