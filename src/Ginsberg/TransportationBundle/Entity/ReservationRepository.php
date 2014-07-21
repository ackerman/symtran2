<?php

namespace Ginsberg\TransportationBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping as RSMAP;
use Monolog\Logger;

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
  */
  public function findUpcomingTrips($date, $date_end)
  {
    $date = new \DateTime($date);
    $date_end = new \DateTime($date_end);
    $params = array('date' => $date, 'date_end' => $date_end);
    $dql = 'SELECT r FROM GinsbergTransportationBundle:Reservation r WHERE 
            ((r.start <= :date AND r.end >= :date_end) 
            OR (r.start <= :date AND r.end >= :date AND r.end <= :date_end) 
            OR (r.start >= :date AND r.start < :date_end AND r.end < :date_end) 
            OR (r.start >= :date AND r.start < :date_end AND r.end >= :date_end))
            AND r.isNoShow is NULL
            AND r.checkout is NULL
            AND r.vehicle is not NULL';
    $query = $this->getEntityManager()->createQuery($dql)->setParameters($params);

    try {
      return $query->getResult();
    } catch (\Doctrine\ORM\NoResultException $ex) {
      return null;
    }
  }
  
  /**
  * Return all reservations that under way (checked out but not checked in yet).
  * 
  * @param date $now Not actually used
  */ 
  public function findOngoingTrips($now)
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
   * @param date $now Not actually used
   */
  public function findCheckinsToday($now) 
  {
    $em = $this->getEntityManager();
    
    // The SQL DATE() function is not supported in Doctrine due to portability issues,
    // so we have to use Doctrine's createNativeQuery(). That involves
    // creating a Result Set Mapping from the SQL results to the class.
    // We do that using the ResultSetMappingBuilder().
    $rsm = new \Doctrine\ORM\Query\ResultSetMappingBuilder($em, 2);
    
    $rsm->addRootEntityFromClassMetadata('Ginsberg\TransportationBundle\Entity\Reservation', 'r', array('created' => 'rcreated'));
    /*$rsm->addJoinedEntityFromClassMetadata('Ginsberg\TransportationBundle\Entity\Program', 'prog', 'r', 'program', array('id' => 'program_id'));
    $rsm->addJoinedEntityFromClassMetadata('Ginsberg\TransportationBundle\Entity\Person', 'p', 'r', 'person', array('id' => 'person_id'));
    //$rsm->addJoinedEntityFromClassMetadata('Ginsberg\TransportationBundle\Entity\Program', 'person_prog', 'p', 'program', array('program_id' => 'program_id'), array('id' => 'wtf_program'));
    $rsm->addJoinedEntityFromClassMetadata('Ginsberg\TransportationBundle\Entity\Destination', 'd', 'r', 'destination', array('id' => 'destination_id'));
    /*$rsm->addJoinedEntityFromClassMetadata('Ginsberg\TransportationBundle\Entity\Program', 'dest_prog', 'd', 'program', array('id' => 'program_id'), array('program_id' => 'goodgod_program'));
    
    $rsm->addJoinedEntityFromClassMetadata('Ginsberg\TransportationBundle\Entity\Series', 's', 'r', 'series', array('id' => 'series_id'));
    $rsm->addJoinedEntityFromClassMetadata('Ginsberg\TransportationBundle\Entity\Ticket', 't', 'r', 'ticket', array('id' => 'ticket_id'));
    //$rsm->addJoinedEntityFromClassMetadata($class, $alias, $parentAlias, $relation, $renamedColumns)
    */

    $nativeSQL = 'SELECT r.id, r.start, r.end, r.checkout, r.checkin, r.program_id from reservation r, vehicle v, person p, program prog WHERE 
            CURRENT_DATE() LIKE DATE(r.checkin) 
            AND r.checkout is not NULL
            AND r.checkin is not NULL
            AND r.vehicle_id is not NULL 
            AND r.person_id = p.id AND r.program_id = prog.id AND r.vehicle_id = v.id';
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
        r.start >= :now AND r.person = :person ORDER BY r.start ASC';
    $query = $this->getEntityManager()->createQuery($dql)->setParameters($params);
    
    try {
      return $query->getResult();
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
	 * @return boolean True if the time slot is free, False if there is a reservation.
	*/
	public function timeSlotAvailable($start, $end, $vehicle) 
  {
    //$logger = $this->get('logger');
    //$logger->info('in _timeSlotAvailable(). Type of vehicle is ' . gettype($vehicle));
    //$request = $this->requestStack->getCurrentRequest();
    
		// find all reservations where the given start time is exactly the same as start
    $em = $this->getEntityManager();
    $query = $em->createQuery(
        'SELECT COUNT(r.vehicle)
          FROM GinsbergTransportationBundle:Reservation r
          WHERE r.start = :start AND r.vehicle = :vehicle')
        ->setParameters(array(':start' => $start, ':vehicle' => $vehicle));
    
    $startEqualOverlap = $query->getSingleScalarResult();

		// find all reservations where the given end time is exactly the same as end
    //$repository = $this->getDoctrine()->getRepository('GinsbergTransportationBundle:Reservation');
		
    $query = $em->createQuery(
      'SELECT COUNT(r.vehicle)
        FROM GinsbergTransportationBundle:Reservation r
        WHERE r.end = :end AND r.vehicle = :vehicle')
      ->setParameters(array(':end' => $end, ':vehicle' => $vehicle));
    
    $endEqualOverlap = $query->getSingleScalarResult();
/*
    $end_equal_overlap = Reservation::model()->count(
			'end = :end AND vehicle_id = :vehicle_id',
			array(
						':end' => $end,
						':vehicle_id' => $vehicle_id,)
		);
*/
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
/*
    $start_overlap = Reservation::model()->count(
      'start < :start AND end > :start AND vehicle_id = :vehicle_id',
      array(
        ':start' => $start,
        ':vehicle_id' => $vehicle_id,
      )
    );
 */

    // find all reservations where the given end time is between start and end.
    // 2pm ----------- 4pm (given $start and end)
    //        3pm ------------- 5pm (existing reservation, will be found)
    $query = $em->createQuery(
      'SELECT COUNT(r.vehicle)
        FROM GinsbergTransportationBundle:Reservation r
        WHERE r.start < :end AND r.end > :end AND r.vehicle = :vehicle')
      ->setParameters(array(':end' => $end, ':vehicle' => $vehicle));
    
    $endOverlap = $query->getSingleScalarResult();
/*
    $end_overlap = Reservation::model()->count(
      'start < :end AND end > :end AND vehicle_id = :vehicle_id',
      array(
        ':end' => $end,
        ':vehicle_id' => $vehicle_id,
      )
    );
*/

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
/*
    $full_overlap = Reservation::model()->count(
      'start > :start AND end < :end AND vehicle_id = :vehicle_id',
      array(
        ':start' => $start,
        ':end' => $end,
        ':vehicle_id' => $vehicle_id,
      )
    );
*/
    // var_dump("vehicle " . $vehicle_id);   // debug
    // var_dump($start_overlap);   // debug
    // var_dump($end_overlap);     // debug
    // var_dump($full_overlap);    // debug
		//$logger->info("In Reservation::time_slot_available, start_equal_overlap = $startEqualOverlap, start_overlap = $startOverlap, end_equal_overlap = $endEqualOverlap, end_overlap = $endOverlap, full_overlap = $fullOverlap, vehicle_id =  " . $vehicle->getId());
    if ( (bool) $startEqualOverlap or (bool) $startOverlap or (bool) $endEqualOverlap or (bool) $endOverlap or (bool) $fullOverlap )
    {
      return FALSE;
    } else {
      return TRUE;
    }
	}
  
  /**
	* Attempts to find an available vehicle belonging to the model's program that
	* and assign it to the current reservation.
  * 
  * @param datetime $start The start time of the reservation
  * @param datetime $end The end time of the reservation
  * @param Vehicle $requestedVehicle Vehicle if admin selected one
	* @return Reservation $entity The reservation with a vehicle assigned if available assigned
	*/
	public function assignReservationToVehicle($entity, $requestedVehicle = FALSE)
  {
    $em = $this->getEntityManager();
    //$logger = $this->get('logger');
		// If a particular car has been requested from the admin Reservation screen,
		// see if that particular vehicle is available
    if ($requestedVehicle) 
    {
      //$logger->info('requestedVehicle must exist');
      // Is the vehicle active and big enough?
      if ($requestedVehicle->getIsActive() && $requestedVehicle->getCapacity() >= $entity->getSeatsRequired())
      {
        //$logger->info('requestedVehicle active and big enough');
        if ($this->timeSlotAvailable($entity->getStart(), $entity->getEnd(), $requestedVehicle)){
					$entity->setVehicle($requestedVehicle);
          return $entity;
				} else 
        {
          $entity->setVehicle(NULL);
          return $entity;
        }
      }
    } else
    { // No particular vehicle was requested
      
      // Find vehicles that are active, in the right program, and have the 
      // required capacity.
			// If reservation is for AR, only let them use AR vehicles
			// If reservation is for PC, only let them use PC vehicles
			// If contract or staff, they can use any vehicle
      $vehicles = array();
      $prog = $entity->getProgram();
      if ($prog->getName() == 'Project Community' || $prog->getName() == 'America Reads')
      {
        //$logger->info('prog = ' . $prog->getName());
        
        $vehicles = $em->getRepository('GinsbergTransportationBundle:Vehicle')->findActiveVehiclesByProgram($entity);
      } else
      {
        //$logger->info('Thinks not PC or AR. prog name = ' . $prog->getName());
        $vehicles = $em->getRepository('GinsbergTransportationBundle:Vehicle')->findActiveVehiclesByCapacity($entity);
        //$vehicles = \Ginsberg\TransportationBundle\Entity\Vehicle::findActiveVehiclesByCapacity($entity);
      }
      
      // For each vehicle, check if the timeslot is free.
			// (this works fine for 10 cars but might not scale to 100 due to the number
			// of queries required.)
			foreach ($vehicles as $vehicle) {
				//$logger->info("Calling time_slot_available for vehicle: " . $vehicle->getName());
				if($this->timeSlotAvailable($entity->getStart(), $entity->getEnd(), $vehicle)){
					$entity->setVehicle($vehicle);
					return $entity;
				} else {
          continue;
        }
			}
      
      // No vehicle available; mark the problem.
			$entity->setVehicle(NULL);
			//$logger->info('In ReservationRepository::assignReservationToVehicle. Assignment failed, no vehicle available.');
			return $entity;
    }
		
  }
  
  /**
   * Returns the last Reservation in the series
   * 
   * @param Series $series The series for which we need the last reservation
   * @return Reservation The last reservation in the series
   */
	public function getLastReservationInSeries($series) {
		$logger = $this->get('logger');
    $logger->info('in _getLastReservationInSeries(). Series id is ' . $series->getId());
    //$request = $this->requestStack->getCurrentRequest();
    
		// find all reservations where the given start time is exactly the same as start
    $em = $this->getEntityManager();
    $query = $em->createQuery(
        'SELECT r
          FROM GinsbergTransportationBundle:Reservation r
          WHERE r.series = :series AND r.start = (SELECT MAX(r1.start) from GinsbergTransportationBundle:Reservation r1 where r1.series = :series)')
        ->setParameter(':series', $series);
    
    $lastReservation = $query->getOneOrNullResult();
    return $lastReservation;
	}
  
  /**
	 * Return the positive or negative interval in days between two dates, 
   * adjusting for time lost due to spring forward.
	 *
	 * Allows a reservation model to reset its date when the reservation is edited.
	 *
   * @param datetime $oldDate The original date of the Reservation
   * @param datetime $newDate The newly requested date from the Reservation form
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
    $logger = $this->get('logger');
    $logger->info('in _getFutureReservationsInSeries. Series id is ' . $entity->getSeries()->getId()) . ' date ($repeatsUntil)= ' . date('Y-m-d H:i:s', $date->getTimestamp()) . ' start = ' . date('Y-m-d H:i:s', $entity->getStart()->getTimestamp());
    //$request = $this->requestStack->getCurrentRequest();
    
		// find all reservations where the given start time is exactly the same as start
    $em = $this->getDoctrine()->getManager();
    $query = $em->createQuery(
        'SELECT r
          FROM GinsbergTransportationBundle:Reservation r
          WHERE r.series = :series AND r.start > :date')
        ->setParameters(array(':series' => $entity->getSeries(), ':date' => $date));
    
    $futureReservationsInSeries = $query->getResult();
    foreach ($futureReservationsInSeries as $res) {
      $logger->info('Id = ' . $res->getId() . ' start = ' . date('Y-m-d H:i:s', $res->getStart()->getTimestamp()) . ' repeatsUntil = ' . ' date ($repeatsUntil)= ' . date('Y-m-d H:i:s', $date->getTimestamp()));
    }
    return $futureReservationsInSeries;
	}
  
  /**
   * Returns an array containing all Reservations in the series 
   * that changed at a certain time. 
   * 
   * The save process for the series may extend over a few milliseconds, so 
   * select for a time period. 
   * 
   * @param Reservation $entity Description
   */
	public function getChangedReservationsInSeries($entity) {
		$logger = $this->get('logger');
    $logger->info('in _getChangedReservationsInSeries. Series id is ' . $entity->getSeries()->getId());
    
    $endRange = $entity->getEnd()->getTimestamp() + 1;
		$endRange = date('Y-m-d H:i:s', $endRange);
    
    $em = $this->getEntityManager();
    $query = $em->createQuery(
        'SELECT r
          FROM GinsbergTransportationBundle:Reservation r
          WHERE r.series = :series AND r.modified BETWEEN :date AND :endRange')
        ->setParameters(array(':series' => $entity->getSeries(), ':date' => $entity->getModified(), ':endRange' => $endRange));
    
    $futureReservationsInSeries = $query->getResult();
    return $futureReservationsInSeries;
	}
}
