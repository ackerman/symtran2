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
    private $ticket_date;

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
    private $is_paid;

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
     * Set ticket_date
     *
     * @param \DateTime $ticketDate
     * @return Ticket
     */
    public function setTicketDate($ticketDate)
    {
        $this->ticket_date = $ticketDate;

        return $this;
    }

    /**
     * Get ticket_date
     *
     * @return \DateTime 
     */
    public function getTicketDate()
    {
        return $this->ticket_date;
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
     * Set is_paid
     *
     * @param boolean $isPaid
     * @return Ticket
     */
    public function setIsPaid($isPaid)
    {
        $this->is_paid = $isPaid;

        return $this;
    }

    /**
     * Get is_paid
     *
     * @return boolean 
     */
    public function getIsPaid()
    {
        return $this->is_paid;
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
