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
      $logger = $this->get('logger');
      // Set local variables needed for fetching different
      // kinds of trips (upcoming trips, ongoing trips, and checkins today)
      $now = date("Y-m-d H:i:s");
      $date =  strtotime(date("Y-m-d"));
      $dateEnd = mktime(0,0,0, date("m", $date), date("d", $date)+1, date("Y", $date));
      $dateEnd = date('Y-m-d H:i:s', $dateEnd);
      $date = date('Y-m-d', $date);
      $logger->info("now  = $now, dateEnd = $dateEnd, date = $date");
      
      $em = $this->getDoctrine()->getManager();
      
      // Find today's upcoming trips.
      // Looks for trips where the reservation has an assigned vehicle
      // and the vehicle has not been checked out yet.
      $upcoming = $em->getRepository('GinsbergTransportationBundle:Reservation')->findUpcomingTrips($date, $dateEnd);
      $ongoing = $em->getRepository('GinsbergTransportationBundle:Reservation')->findOngoingTrips($now);
      $checkinsToday = $em->getRepository('GinsbergTransportationBundle:Reservation')->findCheckinsToday($now);
      
$entities = $em->getRepository('GinsbergTransportationBundle:Reservation')->findAll();

      return array(
        'upcoming' => $upcoming,
        'ongoing' => $ongoing,
        'checkinsToday' => $checkinsToday,
        'entities' => $entities,
        'date' => $date,
        'dateToShow' => $date,
      );
    }
    
    /**
     * Returns reservations for the date selected.
     *
     * @Route("/for_date", name="reservation_search")
     * @Method("POST")
     * @Template("GinsbergTransportationBundle:Reservation:index.html.twig")
     */
    public function searchAction(Request $request)
    {
      $logger = $this->get('logger');
        $logger->info('in searchAction');
        $entity = new Reservation();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        //$logger->info('After handleRequest, dateToShow = ' . date('Y-m-d H:i:s', $form->get('dateToShow')->getData()->getTimestamp()));
        
        
        if ($form->isValid()) {
          $dateToShow = new \DateTime(date('Y-m-d'));
          // If they clicked the "Today" button, show the index page from the 
          // indexAction with ongoing trips, etc.
          if ($form->get('today')->isClicked()) {
            return $this->redirect($this->generateUrl('reservation'));
          } elseif (is_object($form->get('dateToShow')->getData())) {
            $dateToShow = $form->get('dateToShow')->getData();
          }
          //$logger->info('In searchAction, form is valid, dateToShow = ' . $dateToShow->format('c'));
          
          // We have to clone $dateToShow so $dateEnd and $dateToShow aren't 
          // pointing at the same value. We probably don't have to worry about
          // PHP adjusting for daylight savings here, so using $dateEnd->add()
          // should be okay.
          $dateEnd = clone($dateToShow);
          $dateEnd->add(new \DateInterval('P1D'));
          //$logger->info('After changint $dateEnd, dateToShow = ' . $dateToShow->format('c'));
          $ongoing = array();
          $checkinsToday = array();
          $today = new \DateTime(date('Y-m-d'));
          
          $em = $this->getDoctrine()->getManager();
          $entities = $em->getRepository('GinsbergTransportationBundle:Reservation')->findTripsForDate($dateToShow, $dateEnd);
          
          $logger->info('count of entities = ' . count($entities));
          return array(
            'upcoming' => $entities,
            'ongoing' => $ongoing,
            'checkinsToday' => $checkinsToday,
            'dateToShow' => $dateToShow,
            'date' => $today,
            'entities' => array(),
          );
          //return $this->redirect($this->generateUrl('person_search'));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }
 
    /**
    * Creates a form to select the date to go to.
    *
    * @param Reservation $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createSearchForm(Reservation $entity)
    {
        $form = $this->createForm(new ReservationType(), $entity, array(
            'action' => $this->generateUrl('reservation_search'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => "Go to date"));

        return $form;
    }
    /**
     * Allows user to display reservations for a different date.
     *
     * @Route("/search", name="reservation_search_criteria")
     * @Method("GET")
     * @Template("GinsbergTransportationBundle:Reservation:reservation_date_to_show.html.twig")
     */
    public function searchCriteriaAction()
    {
        $entity = new Reservation();
        $form   = $this->createSearchForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
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
      $logger = $this->get('logger');
      $logger->info('In ReservationController::createAction()');    
        $entity = new Reservation();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
          // We'll use the Entity Manager in severall places, so get it now
          $logger->info('Form is valid');
          $em = $this->getDoctrine()->getManager();
          
          
          // Create arrays to hold successful and unsuccessful vehicle 
          // assignments
          $successfulReservations = array();
          $failedReservations = array();
          
          // Is this a repeating reservation?
          $isRepeatingReservation = $form->get('isRepeating')->getData();
          $logger->info("isRepeatingReservation = $isRepeatingReservation");
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

          $resRep = $em->getRepository('GinsbergTransportationBundle:Reservation');
          $entity = $resRep->assignReservationToVehicle($entity, $vehicleRequested);

          $em->flush();
          
          if ($isRepeatingReservation) 
          {
            $entity->getSeries()->addReservation($entity);
            $logger->info('Just saved first reservation in series. entity->getVehicle = ' . $entity->getVehicle());
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
          if ($isRepeatingReservation) {
            $logger->info('This is a repeating reservation');
            
            // The "Repeats Until" field in the Reservation form is not mapped 
            // to the database or the entity, so we get it from the $form
            $repeatsUntil = $form->get('repeatsUntil')->getData();
            $logger->info('repeatsUntil starts out as ' . date('Y-m-d H:i:s', $repeatsUntil->getTimestamp()));
            list($repeatHour, $repeatMinute) = explode(':', $em->getRepository('GinsbergTransportationBundle:Installation')->find(1)->getDailyClose());
            $logger->info('repeatHour = ' . $repeatHour . ', repeatMinute = ' . $repeatMinute);
            $repeatsUntil->setTime($repeatHour, $repeatMinute);
            $logger->info('After setting time, repeatsUntil is ' . date('Y-m-d H:i:s', $repeatsUntil->getTimestamp()));
            
            // Get the datetime one week from the base reservation (the
            // reservation that we are calculating the repetitions from).
            // DO NOT USE PHP DateTime CALCULATIONS. THEY ADJUST RESERVATION 
            // TIMES FOR DAYLIGHT SAVINGS TIME, WHICH IS _NOT_ WHAT WE WANT.
            // E.g., a reservation for 4pm can become a reservation for 3pm or
            // 5pm if you use PHP calculations. 
            $formatter = $this->get('res_utils');
            $repetitionStart = $formatter->getRepeatInterval($entity->getStart());
            $repetitionEnd = $formatter->getRepeatInterval($entity->getEnd());
            
            while ($repetitionStart < $repeatsUntil) {
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
              $reservation->setCreated(new \DateTime());
              
              $reservation->setStart($repetitionStart);
              $reservation->setEnd($repetitionEnd);
              
              $em->persist($reservation);              
              
              $reservation = $em->getRepository('GinsbergTransportationBundle:Reservation')->assignReservationToVehicle($reservation, $vehicleRequested);
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
              
              $reservation->getSeries()->addReservation($reservation);
              
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
                'entities' => $entity->getSeries()->getReservations()));
          } 
          else 
          {
            // It's just a single reservation. Redirect to the Show template  
            // with a success or failure Flash message
            $logger->info('This is a single reservation with Id ' . $entity->getId());
            if ($entity->getVehicle()) {
              $id = $entity->getId();
              $vehicleName = $entity->getVehicle()->getName();
              $this->get('session')->getFlashBag()->add(
                  'sucess',
                  "Success! Reservation $id with vehicle $vehicleName has been created."
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
      $logger = $this->get('logger');
      $logger->info('in ReservationController::createCreateForm');
        $form = $this->createForm(new ReservationType(), $entity, array(
            'action' => $this->generateUrl('reservation_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));
        
        $logger->info('action = ' . $this->generateUrl('reservation_create'));
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
      $logger = $this->get('logger');
      $logger->info('in ReservationController::newAction');
      
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
        $reservationRepository = $em->getRepository('GinsbergTransportationBundle:Reservation'); 
        $entity = $reservationRepository->find($id);

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

          $lastReservationInSeries = $reservationRepository->getLastReservationInSeries($series);
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
          
          $lastReservationInSeries = $em->getRepository('GinsbergTransportationBundle:Reservation')->getLastReservationInSeries($series);
          //$logger->info(var_dump($lastReservationInSeries));
          // Calculate the "original" until date based on the date of last 
          // reservation in the series at Installation's dailyClose time.
          $originalUntilDate = $lastReservationInSeries->getEnd();
          list($repeatHour, $repeatMinute) = explode(':', $em->getRepository('GinsbergTransportationBundle:Installation')->find(1)->getDailyClose());
          $originalUntilDate->setTime($repeatHour, $repeatMinute);
        }
        // Set the unmapped fields
        $form->get('isRepeating')->setData($isRepeating);
        if ($isRepeating) {
         $form->get('repeatsUntil')->setData($originalUntilDate); 
        }
        
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
      
      $logger = $this->get('logger');
      $reservationRepository = $em->getRepository('GinsbergTransportationBundle:Reservation');
      $entity = $reservationRepository->find($id);

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
      $originalUntilDate = NULL;

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

        $lastReservationInSeries = $reservationRepository->getLastReservationInSeries($series);
        //$logger->info(var_dump($lastReservationInSeries));
        // Calculate the "original" until date based on the date of last 
        // reservation in the series at Installation's dailyClose time.
        $originalUntilDate = $lastReservationInSeries->getEnd();
        $logger->info('originalUntilDate starts out as ' . date('Y-m-d H:i:s', $originalUntilDate->getTimestamp()));
        list($repeatHour, $repeatMinute) = explode(':', $em->getRepository('GinsbergTransportationBundle:Installation')->find(1)->getDailyClose());
        $logger->info('repeatHour = ' . $repeatHour . ', repeatMinute = ' . $repeatMinute);
        $originalUntilDate->setTime($repeatHour, $repeatMinute);
        $logger->info('After setting time, originalUntilDate is ' . date('Y-m-d H:i:s', $originalUntilDate->getTimestamp())); 
      }

      $deleteForm = $this->createDeleteForm($id);
      $editForm = $this->createEditForm($entity);
      $editForm->handleRequest($request);

      if ($editForm->isValid()) {
        ///////////////////////////////////////////////////////////////////////
        //////////// BEGIN ACTUAL UPDATE //////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////
         
        // TODO Handle Project Community Destination requirements
        
        $successfulReservations = array();
        $failedReservations = array();
        
        if ($entity->getVehicle() != $originalVehicle) {
          $vehicleRequested = $entity->getVehicle();
        }
        
        $newStartTime = date('H:i:s', $entity->getStart()->getTimestamp());
        $newEndTime = date('H:i:s', $entity->getEnd()->getTimestamp());
			
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


        $entity->setModified(new \DateTime);
        $em->flush();

        $startOrEndChanged = FALSE;
        $needNewVehicle = FALSE;

        // Get the number of days (positive or negative) between the old 
        // start date and the new one. Prevent PHP date calculations from
        // "helping us" by adjusting for daylights savings.
        $startInterval = $reservationRepository->getIntervalInDays($originalStartDate, $entity->getStart());
        // Get the number of days (positive or negative) between the old end date and the new one.
        $endInterval = $reservationRepository->getIntervalInDays($originalEndDate, $entity->getEnd());
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
          
          $reservationRepository->assignReservationToVehicle($entity, $vehicleRequested);
          
          $entity->setModified(new \DateTime);
          $em->flush();
          
          if ($entity->getVehicle()) {
            $successfulReservations[] = $entity;
          } else {
            $failedReservations[] = $entity;
          }
        }

        $editSeries = ($editForm->get('editSeries')->getData()) ? TRUE : FALSE;
        if ($isRepeating && $editSeries && !$isReservationPast) {
          // Initialize some variables
          $reservationsToDelete = array();
          $deletedReservations = 0;

          // calculate final repeat date, making it end at 10:00 pm of the "until" date.
          $repeatsUntil = $editForm->get('repeatsUntil')->getData();
          $logger->info('repeatsUntil = ' . date('Y-m-d H:i:s', $repeatsUntil->getTimestamp()) . ', original_until_date = ' . date('Y-m-d H:i:s', $originalUntilDate->getTimestamp()));
          list($repeatHour, $repeatMinute) = explode(':', $em->getRepository('GinsbergTransportationBundle:Installation')->find(1)->getDailyClose());
          $logger->info('repeatHour = ' . $repeatHour . ', repeatMinute = ' . $repeatMinute);
          $repeatsUntil->setTime($repeatHour, $repeatMinute);
          $logger->info('After setting time, repeatsUntil is ' . date('Y-m-d H:i:s', $repeatsUntil->getTimestamp())); 
          
          if ($repeatsUntil < $originalUntilDate) {
            $logger->info('Going to delete some records. start = ' . date('Y-m-d H:i:s', $entity->getStart()->getTimestamp()) . ', repeat_until = ' . date('Y-m-d H:i:s', $repeatsUntil->getTimestamp()) . ' original_until_date = ' . date('Y-m-d H:i:s', $originalUntilDate->getTimestamp()));
            $reservationsToDelete = $reservationRepository->getFutureReservationsInSeries($entity, $repeatsUntil);
            
            $deletedReservations = 0;
            foreach($reservationsToDelete as $deleteMe) {
              $logger->info('delete_me->id = ' . $deleteMe->getId() . ' delete_me->start = ' . date('Y-m-d H:i:s', $deleteMe->getStart()->getTimestamp()));
              $em->remove($deleteMe);
              $em->flush();
              $deletedReservations++;
            } 
          }
          
          /*// Get the number of days expressed as seconds between the original start time
          // and the new start time. Same for end times. We concatenate the day and the time
          // (calculated separately) in order to avoid having PHP adjust for day lights savings.
          $startInterval = Format::get_repeat_interval(strtotime($originalStartDate), strtotime($entity->start));
          $endInterval = Format::get_repeat_interval(strtotime($originalEndDate), strtotime($entity->end));
          */
          $futureReservations = $reservationRepository->getFutureReservationsInSeries($entity, $entity->getStart());

          foreach($futureReservations as $reservation) {
            $logger->info('starting out in foreach to modify each future reservation, id = ' . $reservation->getId());
            $origStart = $reservation->getStart()->getTimestamp();
            $origEnd = $reservation->getEnd()->getTimestamp();
            
            // TODO: What if someone edits all future reservations starting from
            // a date in the middle of the series? Should we test here for
            // if ($origStart != $entity->getStart())???
            $reservation->setSeatsRequired($entity->getSeatsRequired());
            $reservation->setPerson($entity->getPerson());
            $reservation->setProgram($entity->getProgram());
            $reservation->setDestination($entity->getDestination());
            $reservation->setDestinationText($entity->getDestinationText());
            $reservation->setNotes($entity->getNotes());
            
            
            
            $newStartDay = strtotime(date('Y-m-d', $origStart)) + $startInterval;
            $newEndDay =  strtotime(date('Y-m-d', $origEnd)) + $endInterval;
            $newStartDayAndTime = date('Y-m-d', $newStartDay) . ' ' . $newStartTime;
            $newEndDayAndTime = date('Y-m-d', $newEndDay) . ' ' . $newEndTime;
            $reservation->setStart(new \DateTime($newStartDayAndTime));
            $reservation->setEnd(new \DateTime($newEndDayAndTime));
            $logger->info('in foreach future_reservations, calculated reservation->start = ' . date('Y-m-d H:i:s', $reservation->getStart()->getTimestamp()));
            $logger->info('in foreach future_reservations, calculated reservation->end = ' . date('Y-m-d H:i:s', $reservation->getEnd()->getTimestamp()));
            if ($needNewVehicle) {
              $reservation->setVehicle(NULL);
            }
            $reservation->setCheckout(NULL);
            $reservation->setCheckin(NULL);
            
            $reservation->setModified(new \DateTime());
            
            $em->persist($reservation);
            $em->flush();
            
            $logger->info('Repeating reservation saved: ' );
            
            if ($needNewVehicle) {
              $reservation = $reservationRepository->assignReservationToVehicle($reservation, $vehicleRequested);
              $em->flush();
              if ($reservation->getVehicle()) {
                $successfulReservations[] = $reservation;
                //$logger->info("Needed new vehicle. success count = " . count($successfulReservations));
              } else {
                $failedReservations[] = $reservation;
                //$logger->info("Needed new vehicle. failed count = " . count($failedReservations));
              }
            }
            //$logger->info('in foreach future_reservation, start_datetime now = ' . $start_datetime);
          }
          
          
          // Prepare data for rendering the results page summarizing the 
          // changes made.
          $logger->info("Calling _getChangedReservationsInSeries().");
          $seriesData = $reservationRepository->getChangedReservationsInSeries($entity);
          return $this->render('GinsbergTransportationBundle:Reservation:list_updated_repeating.html.twig', array(
              'deleted' => $deletedReservations, 
              'successes' => count($successfulReservations),
              'failures' => count($failedReservations),
              'vehicleRequested' => $vehicleRequested,
              'entities' => $seriesData,
            )
          );
        } else {
          // It's just a single reservation. Redirect to the Show template  
          // with a success or failure Flash message
          if ($entity->getVehicle()) {
            $id = $entity->getId();
            $vehicleName = $entity->getVehicle()->getName();
            $this->get('session')->getFlashBag()->add(
                'sucess',
                "Success! Reservation $id with vehicle $vehicleName has been updated."
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


            //return $this->redirect($this->generateUrl('reservation_edit', array('id' => $id)));
      }
      
      // Display Edit form because no data submitted yet

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
    
}
