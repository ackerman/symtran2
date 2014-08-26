<?php

namespace Ginsberg\TransportationBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping as RSMAP;

/**
 * ReservationRepository
 *
 * The custom repository for Reservations, holding the business logic
 * that comes into play while creating, updating, and deleting Reservations.
 */
class ReservationRepository extends EntityRepository
{
  /**
  * Return all reservations for today that have not yet been checked out or declared No Shows.
  * I.e., find all reservations where the given start time is between start and end.
  *    today        8am -------------- 8pm today (Today's times)
  *  yesterday, 5pm ------------------------- 6pm tomorrow (reservation spans today, will be found)
  *  yesterday, 5pm -------------- today 6pm (reservation started yesterday and ends today, will be found)
  *    today,            5pm -- today 6pm (reservation started today and ends today, will be found)
  *    today,            5pm ---------------- 6pm tomorrow (reservation started today and ends tomorrow, will be found)
  * 
  * @param date $date The date we are finding reservations for (could be sometime today or a date selected)
  * @param date $date_end The beginning of the day following $date (could be tomorrow, could be based on a date selected)
   * 
   * @return array Array of upcoming trips
  */
  public function findUpcomingTrips($date, $date_end)
  {
    $params = array('date' => $date, 'date_end' => $date_end);
    $dql = 'SELECT r FROM GinsbergTransportationBundle:Reservation r WHERE 
            ((r.start <= :date AND r.end >= :date_end) 
            OR (r.start <= :date AND r.end >= :date AND r.end <= :date_end) 
            OR (r.start >= :date AND r.start < :date_end AND r.end < :date_end) 
            OR (r.start >= :date AND r.start < :date_end AND r.end >= :date_end))
            AND (r.isNoShow is NULL OR r.isNoShow = 0)
            AND (r.checkout is NULL OR r.checkout = 0)
            AND r.vehicle is not NULL';
    $query = $this->getEntityManager()->createQuery($dql)->setParameters($params);

    try {
      return $query->getResult();
    } catch (\Doctrine\ORM\NoResultException $ex) {
      return null;
    }
  }
  
  /**
  * Return all reservations currently under way (checked out but not checked in).
  * 
  *  @return array Array of ongoing trips
  */ 
  public function findOngoingTrips()
  {
    $dql = 'SELECT r FROM GinsbergTransportationBundle:Reservation r WHERE 
            ((r.checkout is not NULL AND r.checkin is NULL)) AND r.vehicle is not NULL';
    $query = $this->getEntityManager()->createQuery($dql);

    try {
      return $query->getResult();
    } catch (\Doctrine\ORM\NoResultException $ex) {
      return null;
    }
  }
  
  /**
   * Return all reservations that were checked in today
   * 
   * @return array Array of reservations checked in today
   */
  public function findCheckinsToday() 
  {
    $em = $this->getEntityManager();
    
    // The SQL DATE() function is not supported in Doctrine due to portability issues,
    // so we have to use Doctrine's createNativeQuery(). That involves
    // creating a Result Set Mapping from the SQL results to the class.
    // We do that using the ResultSetMappingBuilder(). Unfortunately, the 
    // ResultSetMappingBuilder doesn't seem to return associated information, so
    // we will need to do the result set mapping by hand.
    $rsm = new RSMAP;
    $rsm->addEntityResult('Ginsberg\TransportationBundle\Entity\Reservation', 'r');
    $rsm->addFieldResult('r', 'id', 'id');
    $rsm->addFieldResult('r', 'start', 'start');
    $rsm->addFieldResult('r', 'end', 'end');
    $rsm->addFieldResult('r', 'checkout', 'checkout');
    $rsm->addFieldResult('r', 'checkin', 'checkin');
    $rsm->addFieldResult('r', 'notes', 'notes');
    $rsm->addMetaResult('r', 'person_id', 'person_id');
    $rsm->addMetaResult('r', 'vehicle_id', 'vehicle_id');
    
    $nativeSQL = 'SELECT r.id, r.start, r.end, r.checkout, r.checkin, r.notes, r.vehicle_id, r.person_id FROM reservation r 
          WHERE 
            CURRENT_DATE() LIKE DATE(r.checkin) 
            AND r.checkout is not NULL
            AND r.checkin is not NULL
            AND r.vehicle_id is not NULL';
    
    $nativeQuery = $em->createNativeQuery($nativeSQL, $rsm);
    try {
      return $nativeQuery->getResult();
    } catch (\Doctrine\ORM\NoResultException $ex) {
      return null;
    }
  }
  
  public function findTripsForDate($date, $date_end)
  {
    $params = array('date' => $date, 'date_end' => $date_end);
    $dql = 'SELECT r FROM GinsbergTransportationBundle:Reservation r WHERE 
            ((r.start <= :date AND r.end >= :date_end) 
            OR (r.start <= :date AND r.end >= :date AND r.end <= :date_end) 
            OR (r.start >= :date AND r.start < :date_end AND r.end < :date_end) 
            OR (r.start >= :date AND r.start < :date_end AND r.end >= :date_end))
            AND r.vehicle is not NULL';
    $query = $this->getEntityManager()->createQuery($dql)->setParameters($params);

    try {
      return $query->getResult();
    } catch (\Doctrine\ORM\NoResultException $ex) {
      return null;
    }
  }
  
  public function findUpcomingTripsByPerson($now, $person) {
    $params = array('now' => $now, ':person' => $person);
    $dql = 'SELECT r FROM GinsbergTransportationBundle:Reservation r WHERE
        r.start >= :now AND r.person = :person AND r.vehicle IS NOT NULL ORDER BY r.start';
    $query = $this->getEntityManager()->createQuery($dql)->setParameters($params);
    
    try {
      return $query->getResult();
    } catch (\Doctrine\ORM\NoResultException $ex) {
      return NULL;
    }			
  }
  
  /**
   * Returns a Person's past Reservations. 
   * 
   * @param Person $person The Person whose past trips we want to find
   * 
   * @return array 
   */
  public function findPastTripsByPerson($person)
  {
    $now = new \DateTime();
    $em = $this->getEntityManager();
    $query = $em->createQuery('SELECT r FROM GinsbergTransportationBundle:Reservation r 
            WHERE r.end < :now AND r.person = :person AND r.vehicle IS NOT NULL ORDER BY r.start')
            ->setParameters(array('now' => $now, 'person' => $person));
    
    try {
      return $query->getResult();;
    } catch (\Doctrine\ORM\NoResultException $ex) {
      return NULL;
    }	
  }
  
  /**
	 * Given a start and end time and a vehicle id, returns true if the vehicle
	 * is free in the given timeframe.
   * 
	 * @param string $start the start of the time period formatted as 'Y-m-d H:i:s'
	 * @param string $end the end of the time period formatted as 'Y-m-d H:i:s'
	 * @param Vehicle $vehicle The vehicle we are testing for availability
   * 
	 * @return boolean True if the time slot is free, False if there is a reservation.
	*/
	public function timeSlotAvailable($start, $end, $vehicle) 
  {
    $em = $this->getEntityManager();
    // Figure out whether the vehicle is currently in maintenance
    $checkMaintenance = ($vehicle->getMaintenanceStartDate()) ? TRUE : FALSE;
    if ($checkMaintenance) {
      $maintenanceStartDate = $vehicle->getMaintenanceStartDate();
      $maintenanceEndDate = $vehicle->getMaintenanceEndDate();
      if (($start <= $maintenanceStartDate && $end >= $maintenanceEndDate)
            || ($start <= $maintenanceStartDate && $end >= $maintenanceStartDate && $end < $maintenanceEndDate)
            || ($start >= $maintenanceStartDate && $start < $maintenanceEndDate && $end < $maintenanceEndDate)
            || ($start >= $maintenanceStartDate && $start < $maintenanceEndDate && $end > $maintenanceEndDate)) {
        return FALSE;
      }
    }
    // find all reservations where the given start time is exactly the same as start
    $query = $em->createQuery(
        'SELECT COUNT(r.vehicle)
          FROM GinsbergTransportationBundle:Reservation r
          WHERE r.start = :start AND r.vehicle = :vehicle')
        ->setParameters(array(':start' => $start, ':vehicle' => $vehicle));
    
    $startEqualOverlap = $query->getSingleScalarResult();

		// find all reservations where the given end time is exactly the same as end
		
    $query = $em->createQuery(
      'SELECT COUNT(r.vehicle)
        FROM GinsbergTransportationBundle:Reservation r
        WHERE r.end = :end AND r.vehicle = :vehicle')
      ->setParameters(array(':end' => $end, ':vehicle' => $vehicle));
    
    $endEqualOverlap = $query->getSingleScalarResult();

	  // find all reservations where the given start time is between start and end.
    //         3pm ------------ 5pm (given $start and $end)
    // 2pm ------------ 4pm (existing reservation, will be found)
    // also catches situations like this:
    //         3pm ------------ 5pm (given $start and $end)
    // 2pm ---------------------------- 6pm (existing reservation, will be found)
    $query = $em->createQuery(
      'SELECT COUNT(r.vehicle)
        FROM GinsbergTransportationBundle:Reservation r
        WHERE r.start < :start AND r.end > :start AND r.vehicle = :vehicle')
      ->setParameters(array(':start' => $start, ':vehicle' => $vehicle));
    
    $startOverlap = $query->getSingleScalarResult();

    // find all reservations where the given end time is between start and end.
    // 2pm ----------- 4pm (given $start and end)
    //        3pm ------------- 5pm (existing reservation, will be found)
    $query = $em->createQuery(
      'SELECT COUNT(r.vehicle)
        FROM GinsbergTransportationBundle:Reservation r
        WHERE r.start < :end AND r.end > :end AND r.vehicle = :vehicle')
      ->setParameters(array(':end' => $end, ':vehicle' => $vehicle));
    
    $endOverlap = $query->getSingleScalarResult();

    // find all reservations where the given start and end time completely
    // cover a reservation, like so:
    //    2pm --------------------------- 5pm (given $start and $end)
    //            3pm ----------- 4pm (existing reservation, will be found)
    $query = $em->createQuery(
      'SELECT COUNT(r.vehicle)
        FROM GinsbergTransportationBundle:Reservation r
        WHERE r.start > :start AND r.end < :end AND r.vehicle = :vehicle')
      ->setParameters(array(':start' => $start, ':end' => $end, ':vehicle' => $vehicle));
    
    $fullOverlap = $query->getSingleScalarResult();
    
		if ( (bool) $startEqualOverlap or (bool) $startOverlap or (bool) $endEqualOverlap or (bool) $endOverlap or (bool) $fullOverlap )
    {
      return FALSE;
    } else {
      return TRUE;
    }
	}
  
  /**
	* Attempts to find an available vehicle belonging to the Reservation's program
	* and assign it to the current reservation.
  * 
  * @param Reservation $entity The Reservation we are assigning a Vehicle to
  * @param Vehicle $requestedVehicle Optional Vehicle (if admin selected one)
   * 
	* @return Reservation $entity The reservation with a vehicle assigned if available
	*/
	public function assignReservationToVehicle($entity, $requestedVehicle = FALSE)
  {
    $em = $this->getEntityManager();
    // If a particular car has been requested from the admin Reservation screen,
		// see if that particular vehicle is available
    if ($requestedVehicle) 
    {
      // Is the vehicle active and big enough?
      if ($requestedVehicle->getIsActive() && $requestedVehicle->getCapacity() >= $entity->getSeatsRequired())
      {
        if ($this->timeSlotAvailable($entity->getStart(), $entity->getEnd(), $requestedVehicle)){
					$entity->setVehicle($requestedVehicle);
          return $entity;
				} else  {
          $entity->setVehicle(NULL);
          return $entity;
        }
      }
    } else { // No particular vehicle was requested
      
      // Find vehicles that are active, in the right program, and have the 
      // required capacity.
			// -If reservation is for AR, only let them use AR vehicles
			// -If reservation is for PC, only let them use PC vehicles
			// -If contract or staff, they can use any vehicle
      $vehicles = array();
      $prog = $entity->getProgram();
      if ($prog->getName() == 'Project Community' || $prog->getName() == 'America Reads') {
        $vehicles = $em->getRepository('GinsbergTransportationBundle:Vehicle')->findActiveVehiclesByProgram($entity);
      } else {
        $vehicles = $em->getRepository('GinsbergTransportationBundle:Vehicle')->findActiveVehiclesByCapacity($entity);
        //$vehicles = \Ginsberg\TransportationBundle\Entity\Vehicle::findActiveVehiclesByCapacity($entity);
      }
      
      // For each vehicle, check if the timeslot is free.
			// (this works fine for 10 cars but might not scale to 100 due to the number
			// of queries required.)
			foreach ($vehicles as $vehicle) {
				if($this->timeSlotAvailable($entity->getStart(), $entity->getEnd(), $vehicle)){
					$entity->setVehicle($vehicle);
					return $entity;
				} else {
          continue;
        }
			}
      
      // No vehicle available; mark the problem.
			$entity->setVehicle(NULL);
			return $entity;
    }	
  }
  
  /**
   * Returns the last Reservation in the series
   * 
   * @param Series $series The series for which we need the last reservation
   * 
   * @return Reservation The last reservation in the series
   */
	public function getLastReservationInSeries($series) 
  {
		// find the reservation in the series that has the last start time 
    $em = $this->getEntityManager();
    $query = $em->createQuery(
        'SELECT r
          FROM GinsbergTransportationBundle:Reservation r
          WHERE r.series = :series AND r.start = (SELECT MAX(r1.start) from GinsbergTransportationBundle:Reservation r1 where r1.series = :series)')
        ->setParameter('series', $series);
    
    try {
      return $query->getOneOrNullResult();
    } catch (\Doctrine\ORM\NoResultException $ex) {
      return null;
    }
	}
  
  /**
	 * Return the positive or negative interval in days between two dates, 
   * adjusting for time lost due to spring forward.
	 *
	 * Allows a reservation model to reset its date when the reservation is edited.
	 *
   * @param datetime $oldDate The original date of the Reservation
   * @param datetime $newDate The newly requested date from the Reservation form
   * 
   * @return int The interval in seconds between the old and the new dates
	 */
	public function getIntervalInDays($oldDate, $newDate) {
		// Number of seconds in a day
    $oneday = 24*60*60;
		// Get the date with no time for each date
		$oldDate = strtotime(date('Y-m-d', $oldDate->getTimestamp()));
		$newDate = strtotime(date('Y-m-d', $newDate->getTimestamp()));
		// get the interval in seconds
		$interval = $newDate - $oldDate;
		// Divide $interval by 86400 seconds to get the number of days for rounding.
		$interval = $interval/$oneday;
		// If we sprang forward by an hour, the interval is too short, so round up.
		// If we fell back, rounding down will just keep the same number for the interval.
		$interval = round($interval);
		// Now convert $interval into seconds for date manipulation
		return $interval * $oneday;
	}

  /**
   * Returns an array of all Reservations in the series starting after the 
   * provided date
   * 
   * @param Series $series The series we need the future dates in
   * @param datetime $date The date after which we want the reservations
   * @return array An array of future reservations in this series
   */
	public function getFutureReservationsInSeries($entity, $date) 
  {
		// find all reservations where the given start time is exactly the same as start
    $em = $this->getEntityManager();
    $query = $em->createQuery(
        'SELECT r
          FROM GinsbergTransportationBundle:Reservation r
          WHERE r.series = :series AND r.start > :date')
        ->setParameters(array('series' => $entity->getSeries(), ':date' => $date));
    
    try {
      return $query->getResult();
    } catch (\Doctrine\ORM\NoResultException $ex) {
      return null;
    }
	}
  
  /**
   * Returns an array containing all Reservations in the series 
   * that changed at a certain time. 
   * 
   * The save process for the series may extend over a few milliseconds, so 
   * select for a time period. 
   * 
   * @param Reservation $entity Description
   * 
   * @return array
   */
	public function getChangedReservationsInSeries($entity) {
    $endRange = $entity->getEnd()->getTimestamp() + 1;
		$endRange = date('Y-m-d H:i:s', $endRange);
    
    $em = $this->getEntityManager();
    $query = $em->createQuery(
        'SELECT r
          FROM GinsbergTransportationBundle:Reservation r
          WHERE r.series = :series AND r.modified BETWEEN :date AND :endRange')
        ->setParameters(array('series' => $entity->getSeries(), ':date' => $entity->getModified(), ':endRange' => $endRange));
    
    try {
      return $query->getResult();
    } catch (\Doctrine\ORM\NoResultException $ex) {
      return null;
    }
	}
  
  /**
   * Returns an array of all past Reservations. 
   * 
   * @param datetime $now The time before which to find Reservations
   * 
   * @return array|null
   */
  public function findAllPastReservations($now) {
    $em = $this->getEntityManager();
    $query = $em->createQuery('SELECT r FROM GinsbergTransportationBundle:Reservation r 
            WHERE r.end < :now AND r.vehicle IS NOT NULL ORDER BY r.start')
            ->setParameters(array('now' => $now,));
    
    try {
      return $query->getResult();
    } catch (\Doctrine\ORM\NoResultException $ex) {
      return null;
    }
  }
  
  /**
   * Returns Reservations for a given Vehicle for a given day. 
   *
   * @param Vehicle $vehicle The vehicle 
   * @param datetime $date The date
   * 
   * @return array 
   */
  public function findReservationsForVehicleForDate($vehicle, $date)
  {
    $dateEnd = clone($date);
    $dateEnd->add(new \DateInterval('P1D'));
    $params = array('vehicle' => $vehicle, 'date' => $date, 'date_end' => $dateEnd);
    $dql = 'SELECT r FROM GinsbergTransportationBundle:Reservation r WHERE 
            ((r.start <= :date AND r.end >= :date_end) 
            OR (r.start <= :date AND r.end >= :date AND r.end <= :date_end) 
            OR (r.start >= :date AND r.start < :date_end AND r.end < :date_end) 
            OR (r.start >= :date AND r.start < :date_end AND r.end >= :date_end))
            AND r.vehicle = :vehicle';
    $query = $this->getEntityManager()->createQuery($dql)->setParameters($params);

    try {
      return $query->getResult();
    } catch (\Doctrine\ORM\NoResultException $ex) {
      return null;
    }
  }
}
