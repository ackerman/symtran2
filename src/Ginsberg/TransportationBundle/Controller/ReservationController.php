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
          
          // Create arrays to hold successful and unsuccessful vehicle 
          // assignments
          $successfulReservations = array();
          $failedReservations = array();
          
          // Is this a repeating reservation?
          $isRepeatingReservation = ($form->get('isRepeating')->getData()) ? TRUE : FALSE;
          if ($isRepeatingReservation) 
          {
            
          }
          $logger->info("repeatingReservation = $isRepeatingReservation"); 
          
          // Did the admin select a particular vehicle in the Create form?
          $vehicleRequested = $entity->getVehicle();

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
          if ($isRepeatingReservation)
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
          
          if ($isRepeatingReservation) 
          {
            $entity->getSeries()->addReservation($entity);
            $logger->info('First reservation. entity->getVehicle = ' . $entity->getVehicle());
            if ($entity->getVehicle())
            {
              $successfulReservations[] = $entity;
            } 
            else 
            {
              $failedReservations[] = $entity;
            }
          }
          
          // The "isRepeating" field in the Reservation form is not mapped 
          // to the database or the entity, so we get it from the $form
          if ($form->get('isRepeating')->getData() == TRUE)
          {
            $logger->info('This is a repeating reservation');
            
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
              
              $reservation->getSeries()->addReservation($reservation);
              $reservation = $this->_assignReservationToVehicle($reservation, $vehicleRequested);
              if (!$reservation->getVehicle())
              {
                $failedReservations[] = $reservation;
              }
              else
              {
                $successfulReservations[] = $reservation;
              }
              
              // Save the reservation with Vehicle assigned (or failed)
              $em->flush();
              
              // Set up the dates for the next repetition of the reservation
              $repetitionStart = $formatter->getRepeatInterval($repetitionStart);
              $repetitionEnd = $formatter->getRepeatInterval($repetitionEnd);
              
            }
          }
          if ($isRepeatingReservation)
          {
            return $this->render('GinsbergTransportationBundle:Reservation:list_created_repeating.html.twig', array(
                'successes' => count($successfulReservations), 
                'failures' => count($failedReservations),
                'entities' => $reservation->getSeries()->getReservations()));
          } 
          else 
          {
            // It's just a single reservation. Redirect to the Show template  
            // with a success or failure Flash message
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
      $logger = $this->get('logger');
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('GinsbergTransportationBundle:Reservation')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Reservation entity.');
        }

        // Calculations that need to be made before the Edit form is displayed.
        $now = new \DateTime();
        $isReservationPast = ($entity->getEnd() < $now) ? TRUE : FALSE;
        
        // Hold on to the original start and end dates and seatsRequired to 
        // find out if they have changed.
        $originalStartDate = $entity->getStart();
        $originalEndDate = $entity->getEnd();
        $originalSeatsRequired = $entity->getSeatsRequired();
        $originalUntilDate = '';
        
        // Get the entity's vehicle to see if the admin is trying to change 
        // it. If so, we will later save the new id in $requestedVehicle
        $originalVehicle = $entity->getVehicle();
        $vehicleRequested = FALSE;
        
        // Is this a repeating reservation? If so, get the last reservation
        // in the series
        $series = $entity->getSeries();
        $isRepeating = ($series) ? True : False;
        $logger->info("isRepeating = $isRepeating");
        $lastReservationInSeries = NULL;
        if ($isRepeating) {

          $lastReservationInSeries = $this->_getLastReservationInSeries($series);
          //$logger->info(var_dump($lastReservationInSeries));
          // Calculate the "original" until date based on the date of last 
          // reservation in the series at Installation's dailyClose time.
          $originalUntilDate = $lastReservationInSeries->getEnd();
          list($repeatHour, $repeatMinute) = explode(':', $em->getRepository('GinsbergTransportationBundle:Installation')->find(1)->getDailyClose());
          $originalUntilDate->setTime($repeatHour, $repeatMinute);
        }
        
        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'isReservationPast' => $isReservationPast,
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
      $logger = $this->get('logger');
        $form = $this->createForm(new ReservationType(), $entity, array(
            'action' => $this->generateUrl('reservation_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        
        // The form fields "isRepeating" and "repeatsUntil" are not mapped
        // to the entity, so they need to be set based on the data in the 
        // current entity and the end date of the $lastReservationInSeries.
        // 
        // Is this a repeating reservation? If so, get the last reservation
        // in the series
        $series = $entity->getSeries();
        $isRepeating = ($series) ? True : False;
        $logger->info("isRepeating = $isRepeating");
        $lastReservationInSeries = NULL;
        if ($isRepeating) {
          $em = $this->getDoctrine()->getManager();
          
          $lastReservationInSeries = $this->_getLastReservationInSeries($series);
          //$logger->info(var_dump($lastReservationInSeries));
          // Calculate the "original" until date based on the date of last 
          // reservation in the series at Installation's dailyClose time.
          $originalUntilDate = $lastReservationInSeries->getEnd();
          list($repeatHour, $repeatMinute) = explode(':', $em->getRepository('GinsbergTransportationBundle:Installation')->find(1)->getDailyClose());
          $originalUntilDate->setTime($repeatHour, $repeatMinute);
        }
        // Set the unmapped fields
        $form->get('isRepeating')->setData($isRepeating);
        $form->get('repeatsUntil')->setData($originalUntilDate);
        
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

      // Calculations that need to be made before the Edit form is displayed.
      $now = new \DateTime();
      $isReservationPast = ($entity->getEnd() < $now) ? TRUE : FALSE;

      // Hold on to the original start and end dates and seatsRequired to 
      // find out if they have changed.
      $originalStartDate = $entity->getStart();
      $originalEndDate = $entity->getEnd();
      $originalSeatsRequired = $entity->getSeatsRequired();
      $originalUntilDate = '';

      // Get the entity's vehicle to see if the admin is trying to change 
      // it. If so, we will later save the new id in $requestedVehicle
      $originalVehicle = $entity->getVehicle();
      $vehicleRequested = FALSE;

      // Is this a repeating reservation? If so, get the last reservation
      // in the series
      $series = $entity->getSeries();
      $isRepeating = ($series) ? True : False;
      $logger->info("isRepeating = $isRepeating");
      $lastReservationInSeries = NULL;
      if ($isRepeating) {

        $lastReservationInSeries = $this->_getLastReservationInSeries($series);
        //$logger->info(var_dump($lastReservationInSeries));
        // Calculate the "original" until date based on the date of last 
        // reservation in the series at Installation's dailyClose time.
        $originalUntilDate = $lastReservationInSeries->getEnd();
        list($repeatHour, $repeatMinute) = explode(':', $em->getRepository('GinsbergTransportationBundle:Installation')->find(1)->getDailyClose());
        $originalUntilDate->setTime($repeatHour, $repeatMinute);
      }

      $deleteForm = $this->createDeleteForm($id);
      $editForm = $this->createEditForm($entity);
      $editForm->handleRequest($request);

      if ($editForm->isValid()) {
        // TODO Handle Project Community Destination requirements
        if ($entity->getVehicle() != $originalVehicle) {
          $vehicleRequested = $entity->getVehicle();
        }

        // If reservation is in the past, don't let them change start, end, 
        // seats required, or vehicle_id. This would trigger reassigning 
        // the vehicle, which doesn't make sense for a past reservation.
        if ($isReservationPast) {
          $entity->setSeatsRequired($originalSeatsRequired);
          $entity->setStart($originalStartDate);
          $entity->setEnd($originalEndDate);
          $entity->setVehicle($originalVehicle);
        }

        // In Yii, we had to set the checkout and checkin values based
        // on the POST data. Irrelevant here? Or not?



        $em->flush();

        $startOrEndChanged = FALSE;
        $needNewVehicle = FALSE;

        // Get the number of days (positive or negative) between the old 
        // start date and the new one. Prevent PHP date calculations from
        // "helping us" by adjusting for daylights savings.
        $startInterval = $this->_getIntervalInDays($originalStartDate, $entity->getStart());
        // Get the number of days (positive or negative) between the old end date and the new one.
        $endInterval = $this->_getIntervalInDays($originalEndDate, $entity->getEnd());
        // Save the start and end times so we can set them independently from 
        // the dates. This allows us to avoid problems with PHP's automatic adjustment for daylight savings time
        $logger->info('start interval = ' . $startInterval . ', ' . $startInterval * 24*60*60);
        $logger->info('end interval = ' . $endInterval . ', ' . $endInterval * 24*60*60);
        
        // If they used to only need a car, but now need a van or the reverse,
        // they need a new vehicle now.
        if (($originalSeatsRequired <= 5 && $entity->getSeatsRequired() > 5) || ($originalSeatsRequired > 5 && $entity->getSeatsRequired() <= 5)) {
          $needNewVehicle = TRUE;
        }
        
        // Check if the reservation times have changed. If yes, then we need
        // to find out if a vehicle is available for the new times.
        // TODO should also check for available vehicle if vehicle not currently assigned.
        if ( !($originalStartDate == $entity->getStart()) || !($originalEndDate == $entity->getEnd()) ) {
          $startOrEndChanged = TRUE;
          $needNewVehicle = TRUE;
        }
        // If the admin has requested a particular vehicle, we need to request 
        // that vehicle
        if ($vehicleRequested) {
          $needNewVehicle = TRUE;
        }
        
        // If this reservation is not in the past and it needs a new vehicle,
        // save the reservation and then attempt to assign it to a new vehicle.
        if ($needNewVehicle && !$isReservationPast) {
          $logger->info('In original reservation. id = ' . $entity->getId());
          $entity->setVehicle(NULL);
          
          $em->flush();
          
          $this->_assignReservationToVehicle($vehicleRequested);
          
          $em->flush();
        }

        $editSeries = ($form->get('editSeries')->getData()) ? TRUE : FALSE;
        if ($repeating && $editSeries && !$isReservationPast) {
          // Initialize some variables
          $reservationsToDelete = array();
          $deletedReservations = 0;

          // calculate final repeat date, making it end at 10:00 pm of the "until" date.
          $repeatUntil = $form->get('repeatsUntil')->getData();
          $logger->info('repeat_until = ' . date('Y-m-d H:i:s', $repeatUntil->getTimestamp()) . ', original_until_date = ' . date('Y-m-d H:i:s', $originalUntilDate->getTimestamp()));
          if ($repeatUntil < $originalUntilDate) {
            $logger->info('Going to delete some records. repeat_until = ' . date('Y-m-d H:i:s', $repeatUntil->getTimestamp()) . ' original_until_date = ' . date('Y-m-d H:i:s', $originalUntilDate->getTimestamp()));
            $reservationsToDelete = $this->_getFutureReservationsInSeries($series, $repeatUntil);
            $reservationsToDelete = $reservationsToDelete->getData();
          }

          /*// Get the number of days expressed as seconds between the original start time
          // and the new start time. Same for end times. We concatenate the day and the time
          // (calculated separately) in order to avoid having PHP adjust for day lights savings.
          $startInterval = Format::get_repeat_interval(strtotime($originalStartDate), strtotime($entity->start));
          $endInterval = Format::get_repeat_interval(strtotime($originalEndDate), strtotime($entity->end));
          */
          $futureReservations = $this->_getFutureReservationsInSeries($series, $entity->getStart());

          foreach($futureReservations as $reservation) {
            $logger->info('starting out in foreach, id = ' . $reservation->getId() . '  orig reservation->start: ' . $reservation->getStart() );
            $logger->info('starting out in foreach, id = ' . $reservation->getId() . '  orig reservation->end: ' . $reservation->getEnd() );
            $orig_start = strtotime($reservation->getStart());
            $orig_end = strtotime($reservation->getEnd());
            $reservation->attributes=$_POST['Reservation'];
            $reservation->date_edited = date('Y-m-d H:i:s');
            $new_start_day = strtotime(date('Y-m-d', $orig_start)) + $startInterval;
            $new_end_day =  strtotime(date('Y-m-d', $orig_end)) + $endInterval;
            $new_start_day_and_time = date('Y-m-d', $new_start_day) . ' ' . $new_start_time;
            $new_end_day_and_time = date('Y-m-d', $new_end_day) . ' ' . $new_end_time;
            $reservation->setStart($new_start_day_and_time);
            $reservation->setEnd($new_end_day_and_time);
            $logger->info('in foreach future_reservations, calculated reservation->start = ' . $reservation->getStart());
            $logger->info('in foreach future_reservations, calculated reservation->end = ' . $reservation->getEnd());
            if ($needNewVehicle) {
              $reservation->setVehicle(NULL);
            }
            $reservation->setCheckout(NULL);
            $reservation->setCheckin(NULL);
            if ($reservation->save()) {
              $logger->info('Repeating reservation saved');
            }
            if ($needNewVehicle) {
              $reservation->assign_reservation_to_vehicle($vehicleRequested);
            }
            //$logger->info('in foreach future_reservation, start_datetime now = ' . $start_datetime);
          }
          if ($reservationsToDelete != '') {
            foreach($reservationsToDelete as $deleteMe) {
              $logger->info('delete_me->id = ' . $deleteMe->getId() . ' delete_me->start = ' . $deleteMe->getStart());
              $deleteMe->delete();
              $deletedReservations++;
            }
          }
          $seriesData = Reservation::get_changed_reservations_in_series($series_id, $entity->date_edited);
          $this->render('view_repeating',array(
            'action'=>'Updated',
            'modified'=>$entity->getModified(),
            'isReservationPast'=>$isReservationPast,
            'vehicleRequested' => $vehicleRequested,
            'series'=>$entity->getSeries(),
            'seriesData'=>$seriesData,
            'deletedReservationCount' => $deletedReservations,
          ));
        } else {
          //$this->redirect(array('reservation/view/' . $entity->id));
          // It would be better if this were a redirect(), but I need to pass in the $vehicleRequested variable
          $this->render('view', array(
                                      'model'=>$entity,
                                      'vehicle_requested'=>$vehicleRequested,
                                      ));
          // Prevent the rest of this controller from being evaluated and displaying the update view.
          return TRUE;
        }


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
	* @return Reservation $entity The reservation with a vehicle assigned if available assigned
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
      if ($requestedVehicle->getIsActive() && $requestedVehicle->getCapacity() >= $entity->getSeatsRequired())
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
				} 
			}
      
      // No vehicle available; mark the problem.
			$entity->setVehicle(NULL);
			$logger->info('In _assignReservationToVehicle. Assignment failed, no vehicle available.');
			return $entity;
    }
		/*if ($requestedVehicle) {
			// Is the vehicle active and big enough?
			$vehicle = Vehicle::model()->findByPk($requestedVehicle);
			$logger->info('this->id = ' . $this->id);
			$logger->info('vehicle->name = ' . $vehicle->name);
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
				$logger->info('prog name = ' . $prog->name);
				$vehicles = Vehicle::find_active_vehicles_by_program($this->program_id, $this->seats_required);
			} else {
				$logger->info('Thinks not PC or AR. prog name = ' . $prog->name);
				$vehicles = Vehicle::find_active_vehicles($this->seats_required);
			}
      
			$vehicles = '';
			$prog = Program::model()->findByPk($this->program_id);
			if($prog->name == "Project Community" || $prog->name == "America Reads") {
				$logger->info('prog name = ' . $prog->name);
				$vehicles = Vehicle::find_active_vehicles_by_program($this->program_id, $this->seats_required);
			} else {
				$logger->info('Thinks not PC or AR. prog name = ' . $prog->name);
				$vehicles = Vehicle::find_active_vehicles($this->seats_required);
			}
       


			// For each vehicle, check if the timeslot is free.
			// (this works fine for 10 cars but might not scale to 100 due to the number
			// of queries required.)
			foreach ($vehicles as $vehicle) {
				$logger->info("Calling time_slot_available for start: " . $this->start . ", end: " . $this->end . ", vehicle: " . $vehicle->id);
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
				$logger->info('In assign_reservation_to_vehicle. Assignment and save failed. Res id = ' . $this->id . ' Vehicle_id = ' . $this->vehicle_id);
			} else {
				$logger->info('In assign_reservation_to_vehicle. Assignment failed, but save succeeded. Res id = ' . $this->id . ' Vehicle_id = ' . $this->vehicle_id);
			}
			return False;
		}
      */
  }
  
  /**
   * Returns the last Reservation in the series
   * 
   * @param Series $series The series for which we need the last reservation
   * @return Reservation The last reservation in the series
   */
	private function _getLastReservationInSeries($series) {
		$logger = $this->get('logger');
    $logger->info('in _getLastReservationInSeries(). Series id is ' . $series->getId());
    //$request = $this->requestStack->getCurrentRequest();
    
		// find all reservations where the given start time is exactly the same as start
    $em = $this->getDoctrine()->getManager();
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
	 */
	private function _getIntervalInDays($oldDate, $newDate) {
		// Number of seconds in a day
    $oneday = 24*60*60;
		// Get the date with no time for each date
		$oldDate = strtotime(date('Y-m-d', strtotime($oldDate)));
		$newDate = strtotime(date('Y-m-d', strtotime($newDate)));
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
	private function _getFutureReservationsInSeries($series, $date) 
  {
    $logger = $this->get('logger');
    $logger->info('in _getFutureReservationsInSeries. Series id is ' . $series->getId());
    //$request = $this->requestStack->getCurrentRequest();
    
		// find all reservations where the given start time is exactly the same as start
    $em = $this->getDoctrine()->getManager();
    $query = $em->createQuery(
        'SELECT r
          FROM GinsbergTransportationBundle:Reservation r
          WHERE series = :series AND start > :date')
        ->setParameters(array(':series' => $series, ':date' => $date));
    
    $futureReservationsInSeries = $query->getResult();
    return $futureReservationsInSeries;
	}
}
