<?php

namespace Ginsberg\TransportationBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Ginsberg\TransportationBundle\Entity\Reservation;
use Ginsberg\TransportationBundle\Entity\Series;
use Ginsberg\TransportationBundle\Form\ReservationType;
use \DateTime;

/**
 * Reservation controller.
 *
 * @Route("/reservation")
 */
class ReservationController extends Controller
{

    /**
     * Lists all Reservation entities.
     *
     * @Route("/", name="reservation")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
      // Set local variables needed for fetching different
      // kinds of trips (upcoming trips, ongoing trips, and checkins today)
      $now = date("Y-m-d H:i:s");
      $date =  strtotime(date("Y-m-d"));
      $date_end = mktime(0,0,0, date("m", $date), date("d", $date)+1, date("Y", $date));
      $date_end = date('Y-m-d H:i:s', $date_end);
      $date = date('Y-m-d H:i:s', $date);
      $em = $this->getDoctrine()->getManager();

      // Find today's upcoming trips.
      // Looks for trips where the reservation has an assigned vehicle
      // and the vehicle has not been checked out yet.
      $upcoming = $em->getRepository('GinsbergTransportationBundle:Reservation')->findUpcomingTrips($date, $date_end);
      $ongoing = $em->getRepository('GinsbergTransportationBundle:Reservation')->findOngoingTrips($now);
      $checkinsToday = $em->getRepository('GinsbergTransportationBundle:Reservation')->findCheckinsToday($now);
      
$entities = $em->getRepository('GinsbergTransportationBundle:Reservation')->findAll();

      return array(
        'upcoming' => $upcoming,
        'ongoing' => $ongoing,
        'checkinsToday' => $checkinsToday,
        'entities' => $entities,
        'date' => $date,
      );
    }
    /**
     * Creates a new Reservation entity.
     *
     * @Route("/", name="reservation_create")
     * @Method("POST")
     * @Template("GinsbergTransportationBundle:Reservation:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Reservation();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
          // We'll use the Entity Manager in severall places, so get it now
          $em = $this->getDoctrine()->getManager();
          
          $logger = $this->get('logger');
            
          // Is this a repeating reservation?
          $repeatingReservation = ($form->get('isRepeating')->getData()) ? TRUE : FALSE;
          $logger->info("repeatingReservation = $repeatingReservation"); 
          
          // Did the admin select a particular vehicle in the Create form?
          $vehicleRequested = $entity->getVehicle();
          $logger->info("vehicleRequested = $vehicleRequested");
          $logger->info("vehicleRequested is of type " . gettype($vehicleRequested));
          
          // TODO: figure out how to handle special PC requirements for Destination
          
          // Even if the admin requested a particular vehicle, we don't want 
          // $entity->vehicle set yet, because we haven't checked it for 
          // availablity yet. The vehcle_id of the requested vehicle is 
          // stored in $vehicleRequested.
          if ($vehicleRequested) {
            $entity->setVehicle(NULL);
          }
          
          // If this is a repeating reservation, create the Series and get the
          // series id to set in the Reservation entity.
          if ($repeatingReservation)
          {
            $seriesEntity = new Series();
            $em->persist($seriesEntity);
            $em->flush();
            $entity->setSeries($seriesEntity);
          }
          
          // If the reservation can be successfully saved, attempt to assign 
          // it to a vehicle.
          $em->persist($entity);
          $logger->info('Just persisted reservation entity prior to assigning vehicle');
          $em->flush(); 
         
          $logger->info('vehicleRequested is of type ' . gettype($vehicleRequested));
          //$vehicle = $em->getRepository('Vehicle')->find($);

          $entity = $this->_assignReservationToVehicle($entity, $vehicleRequested);

          $em->flush();
          
         
          
          // The "isRepeating" field in the Reservation form is not mapped 
          // to the database or the entity, so we get it from the $form
          if ($form->get('isRepeating')->getData() == TRUE)
          {
            $logger->info('This is a repeating reservation');
            $repeatingReservationsCreated = 0; // counter
            $noVehicleAvailable = array(); // keep track of failed reservations
          
            // The "Repeats Until" field in the Reservation form is not mapped 
            // to the database or the entity, so we get it from the $form
            $repeatsUntil = $form->get('repeatsUntil')->getData();
            list($repeatHour, $repeatMinute) = explode(':', $em->getRepository('GinsbergTransportationBundle:Installation')->find(1)->getDailyClose());
            $repeatsUntil->setTime($repeatHour, $repeatMinute);
            
            // Get the datetime one week from the base reservation (the
            // reservation that we are calculating the repetitions from).
            // DO NOT USE PHP DateTime CALCULATIONS. THEY ADJUST RESERVATION 
            // TIMES FOR DAYLIGHT SAVINGS TIME, WHICH IS _NOT_ WHAT WE WANT.
            // E.g., a reservation for 4pm can become a reservation for 3pm or
            // 5pm if you use PHP calculations. 
            $formatter = $this->get('res_utils');
            $repetitionStart = $formatter->getRepeatInterval($entity->getStart());
            $repetitionEnd = $formatter->getRepeatInterval($entity->getEnd());
            
            while ($repetitionStart < $repeatsUntil)
            {
              // Create reservation for new date
              $reservation = new Reservation();
              $reservation->setSeatsRequired($entity->getSeatsRequired());
              $reservation->setSeries($entity->getSeries());
              $reservation->setPerson($entity->getPerson());
              $reservation->setProgram($entity->getProgram());
              $reservation->setVehicle(NULL);
              $reservation->setDestination($entity->getDestination());
              $reservation->setDestinationText($entity->getDestinationText());
              $reservation->setNotes($entity->getNotes());
              $reservation->setCheckout(NULL);
              $reservation->setCheckin(NULL);
              $reservation->setCreated(new DateTime());
              
              $reservation->setStart($repetitionStart);
              $reservation->setEnd($repetitionEnd);
              
              $em->persist($reservation);
              $em->flush();
              
              if (!$this->_assignReservationToVehicle($reservation))
              {
                array_push($noVehicleAvailable, $reservation);
              }
              
              $em->flush();
              $repeatingReservationsCreated++;
              
              // Set up the dates for the next repetition of the reservation
              $repetitionStart = $formatter->getRepeatInterval($repetitionStart);
              $repetitionEnd = $formatter->getRepeatInterval($repetitionEnd);
              
            }
          }
          if ($repeatingReservation)
          {
            $this->get('session')->getFlashBag()->add(
                'repeating',
                'Created ' . $repeatingReservationsCreated . ' reservations.'
            );
            return $this->redirect($this->generateUrl('reservation_show', array('id' => $entity->getId())));
          } 
           if ($entity->getVehicle()) {
            $this->get('session')->getFlashBag()->add(
                'sucess',
                'Success! Reservation '. $entity->getId() . ' with vehicle ' . $entity-getVehicle()->getName() . ' has been created.'
            );
            return $this->redirect($this->generateUrl('reservation_show', array('id' => $entity->getId())));
          }
          else
          {
            $this->get('session')->getFlashBag()->add(
                'failure',
                'Sorry! No vehicle is available at the requested time.'
            );
            return $this->redirect($this->generateUrl('reservation_show', array('id' => $entity->getId())));
          }
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a Reservation entity.
    *
    * @param Reservation $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Reservation $entity)
    {
        $form = $this->createForm(new ReservationType(), $entity, array(
            'action' => $this->generateUrl('reservation_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Reservation entity.
     *
     * @Route("/new", name="reservation_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Reservation();
        $entity->setCreated(new \DateTime());
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Reservation entity.
     *
     * @Route("/{id}", name="reservation_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('GinsbergTransportationBundle:Reservation')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Reservation entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Reservation entity.
     *
     * @Route("/{id}/edit", name="reservation_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('GinsbergTransportationBundle:Reservation')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Reservation entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Reservation entity.
    *
    * @param Reservation $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Reservation $entity)
    {
        $form = $this->createForm(new ReservationType(), $entity, array(
            'action' => $this->generateUrl('reservation_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Reservation entity.
     *
     * @Route("/{id}", name="reservation_update")
     * @Method("PUT")
     * @Template("GinsbergTransportationBundle:Reservation:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('GinsbergTransportationBundle:Reservation')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Reservation entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('reservation_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Reservation entity.
     *
     * @Route("/{id}", name="reservation_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('GinsbergTransportationBundle:Reservation')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Reservation entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('reservation'));
    }

    /**
     * Creates a form to delete a Reservation entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('reservation_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
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
	private function _timeSlotAvailable($start, $end, $vehicle) 
  {
    $logger = $this->get('logger');
    $logger->info('in _timeSlotAvailable(). Type of vehicle is ' . gettype($vehicle));
    //$request = $this->requestStack->getCurrentRequest();
    
		// find all reservations where the given start time is exactly the same as start
    $em = $this->getDoctrine()->getManager();
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
		$logger->info("In Reservation::time_slot_available, start_equal_overlap = $startEqualOverlap, start_overlap = $startOverlap, end_equal_overlap = $endEqualOverlap, end_overlap = $endOverlap, full_overlap = $fullOverlap, vehicle_id =  " . $vehicle->getId());
    if ( (bool) $startEqualOverlap or (bool) $startOverlap or (bool) $endEqualOverlap or (bool) $endOverlap or (bool) $fullOverlap )
    {
      return False;
    } 
    return True;
	}
  
  /**
	* Attempts to find an available vehicle belonging to the model's program that
	* and assign it to the current reservation.
  * 
  * @param datetime $start The start time of the reservation
  * @param datetime $end The end time of the reservation
  * @param Vehicle $requestedVehicle Vehicle if admin selected one
	* @return array $reservationsToSave Array (possibly empty) of reservations with vehicles assigned
	*/
	private function _assignReservationToVehicle($entity, $requestedVehicle = FALSE)
  {
    $em = $this->getDoctrine()->getManager();
    $logger = $this->get('logger');
		// If a particular car has been requested from the admin Reservation screen,
		// see if that particular vehicle is available
    if ($requestedVehicle) 
    {
      $logger->info('requestedVehicle must exist');
      // Is the vehicle active and big enough?
      if ($requestedVehicle->getIsActive() && $requestedVehicle->getCapacity() >= $entity->getCapacity())
      {
        $logger->info('requestedVehicle active and big enough');
        if ($this->_timeSlotAvailable($entity->getStart(), $entity->getEnd(), $requestedVehicle)){
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
        $logger->info('prog = ' . $prog->getName());
        
        $vehicles = $em->getRepository('GinsbergTransportationBundle:Vehicle')->findActiveVehiclesByProgram($entity);
      } else
      {
        $logger->info('Thinks not PC or AR. prog name = ' . $prog->getName());
        $vehicles = $em->getRepository('GinsbergTransportationBundle:Vehicle')->findActiveVehiclesByCapacity($entity);
        //$vehicles = \Ginsberg\TransportationBundle\Entity\Vehicle::findActiveVehiclesByCapacity($entity);
      }
      
      // For each vehicle, check if the timeslot is free.
			// (this works fine for 10 cars but might not scale to 100 due to the number
			// of queries required.)
			foreach ($vehicles as $vehicle) {
				//$logger->info("Calling time_slot_available for start: " . $entity->getStart() . ", end: " . $entity->getEnd() . ", vehicle: " . $entity->getVehicle()->getName());
				if($this->_timeSlotAvailable($entity->getStart(), $entity->getEnd(), $vehicle)){
					$entity->setVehicle($vehicle);
					return $entity;
				} else {

				}
			}
      
      // No vehicle available; mark the problem.
			$entity->setVehicle(NULL);
			$logger->info('In _assignReservationToVehcle. Assignment failed, no vehicle available.');
			return $entity;
    }
		/*if ($requestedVehicle) {
			// Is the vehicle active and big enough?
			$vehicle = Vehicle::model()->findByPk($requestedVehicle);
			Yii::log('this->id = ' . $this->id, 'info', 'system.debug');
			Yii::log('vehicle->name = ' . $vehicle->name, 'info', 'system.debug');
			if ($vehicle->active && $vehicle->capacity >= $this->seats_required) {
				if(Reservation::time_slot_available($this->start, $this->end, $vehicle->id)){
					$this->vehicle_id = $vehicle->id;
					$this->save();
					return True;
				}
			}
     */

			// The requested vehicle was not available; mark the problem.
			


			// Find vehicles that are active, in the right program, and have the required capacity
			// If reservation is for AR, only let them use AR vehicles
			// If reservation is for PC, only let them use PC vehicles
			// If contract or staff, they can use any vehicle
     /* 
			$prog = Program::model()->findByPk($this->program_id);
			if($prog->name == "Project Community" || $prog->name == "America Reads") {
				Yii::log('prog name = ' . $prog->name, "info", "System.debug");
				$vehicles = Vehicle::find_active_vehicles_by_program($this->program_id, $this->seats_required);
			} else {
				Yii::log('Thinks not PC or AR. prog name = ' . $prog->name, "info", "System.debug");
				$vehicles = Vehicle::find_active_vehicles($this->seats_required);
			}
      
			$vehicles = '';
			$prog = Program::model()->findByPk($this->program_id);
			if($prog->name == "Project Community" || $prog->name == "America Reads") {
				Yii::log('prog name = ' . $prog->name, "info", "System.debug");
				$vehicles = Vehicle::find_active_vehicles_by_program($this->program_id, $this->seats_required);
			} else {
				Yii::log('Thinks not PC or AR. prog name = ' . $prog->name, "info", "System.debug");
				$vehicles = Vehicle::find_active_vehicles($this->seats_required);
			}
       


			// For each vehicle, check if the timeslot is free.
			// (this works fine for 10 cars but might not scale to 100 due to the number
			// of queries required.)
			foreach ($vehicles as $vehicle) {
				Yii::log("Calling time_slot_available for start: " . $this->start . ", end: " . $this->end . ", vehicle: " . $vehicle->id, "info", "System.debug");
				if(Reservation::time_slot_available($this->start, $this->end, $vehicle->id)){
					$this->vehicle_id = $vehicle->id;
					$this->save();
					return True;
				} else {

				}
			}
			// No vehicle available; mark the problem.
			$this->vehicle_id = Null;
			//$this->program = Null;
			if(!$this->save()) {
				Yii::log('In assign_reservation_to_vehicle. Assignment and save failed. Res id = ' . $this->id . ' Vehicle_id = ' . $this->vehicle_id, 'info', 'system.debug');
			} else {
				Yii::log('In assign_reservation_to_vehicle. Assignment failed, but save succeeded. Res id = ' . $this->id . ' Vehicle_id = ' . $this->vehicle_id, 'info', 'system.debug');
			}
			return False;
		}
      */
  }

}
