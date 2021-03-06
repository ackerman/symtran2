<?php

namespace Ginsberg\TransportationBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Ginsberg\TransportationBundle\Entity\Reservation;
use Ginsberg\TransportationBundle\Entity\Person;
use Ginsberg\TransportationBundle\Entity\Series;
use Ginsberg\TransportationBundle\Form\ReservationType;
use Ginsberg\TransportationBundle\Form\PersonType;

/**
 * Controller for public-facing area of Transportation website
 * 
 * @Route("/")
 */
class SiteController extends Controller
{
	/**
	 * Route users to appropriate action, and display index to users who meet all conditions
	 *
	 * Route users to the appropriate action for their status in the
	 * PTS MVR database and the Ginsberg Transportation system.
	 * Only users who are approvedin both systems and have agreed to the terms
	 * and contitions are routed to the actual index page
  *
  * @Route("/", name="site")
  * @Method("GET")
  * @Template()
  */
	public function indexAction()
	{
    $logger = $this->get('logger');
		$logger->info('In SiteController::actionIndex');
    
    $em = $this->getDoctrine()->getManager();
    
    $provider = $this->get('user_provider');
		$uniqname = $provider->get_uniqname();

		// User::is_eligible will either return the name of the first eligibility group found
		// for the user, or false. Save the name of the $ldapGroup for later.
		$ldapGroup = $provider->is_eligible();
    $logger->info("ldap group = $ldapGroup");

		// If the user is not eligible, (that is, they are not in a subgroup of the group
		// 'ginsberg transpo eligible'), send them to the 'ineligible' view.
		if ($ldapGroup == FALSE) {
			return $this->render('GinsbergTransportationBundle:Site:ineligible.html.twig', array(
				'nav' => FALSE,
          'name' => $provider->get_first_name(),
			));
		} else {
			// Then, we need to figure out if the user should see the
			// registration, pending user, ineligible, or rejected user screens instead of
			// the reservation page:

			// First, what is their status with PTS? If request didn't return 200, redirect
			// to appropriate error page
			$pts_json = $provider->get_pts_info($uniqname);
			$pts_status = '';
			$done = false;

			//$pts_json->mvr_status = 'Approved'; //	***FOR TESTING***
			//$pts_json->http_code = 405; // ***FOR TESTING***

			// Continue processing if http_code == 200 ($done is false), otherwise, display error view
			switch($pts_json->http_code) {
				case 200: // We got a status back from PTS. Process it after the switch.
					$pts_status = $pts_json->mvr_status;
					//$logger->info('pts_status = ' . $pts_status, 'info', 'System.debug');
					break;
				case 500: // contacted PTS, but status retrieval failed
					$done = true;
					return $this->render('GinsbergTransportationBundle:Site:problem.html.twig', array(
            'nav' => FALSE,
            'name' => $provider->get_first_name(),
          ));
				case 405: // Ginsberg not delegate
					$done = true;
					return $this->render('GinsbergTransportationBundle:Site:ginsberg_not_delegate.html.twig', array(
            'nav' => FALSE,
            'name' => $provider->get_first_name(),
          ));
				case 404: // Student not in MVR database
					$done = true;
					return $this->render('GinsbergTransportationBundle:Site:not_in_mvr.html.twig', array(
            'nav' => FALSE,
            'name' => $provider->get_first_name(),
          ));
				case 401: // Unauthorized
					$done = true;
					return $this->render('GinsbergTransportationBundle:Site:problem.html.twig', array(
            'nav' => FALSE,
            'name' => $provider->get_first_name(),
          ));
				case 400: // Bad request, such as no uniqname
					$done = true;
					return $this->render('GinsbergTransportationBundle:Site:problem.html.twig', array(
            'nav' => FALSE,
            'name' => $provider->get_first_name(),
          ));
			}

			// If we got a 200 code, continue processing. Otherwise, we're done.
			if (!$done) {
        $personRepository = $em->getRepository('GinsbergTransportationBundle:Person');
				$person = $personRepository->findByUniqname($uniqname);
        if (is_array($person)) {
          if (count($person) == 1) {
            $person = $person[0];
          }
        }
				// If the person already set their program manually, don't change it.
				// Otherwise, set it based on the eligibility group they are in.
        // We know the person has a program set if isTermsAgreed is true, since
        // they had to set a program on the registration form.
				$programRepository = $em->getRepository('GinsbergTransportationBundle:Program');
				$program = $programRepository->findByEligibilityGroup($ldapGroup);
        if (is_array($program)) {
          if (count($program) == 1) {
            $program = $program[0];
          }
        }
        //$logger->info('User\'s program = ' . $program->getName() . 'mvr_status: ' . $pts_json->mvr_status);
				if ($person && $program && !$person->getIsTermsAgreed()) {
					$person->setProgram($program);
				}

				switch($pts_json->mvr_status) {
					case "Approved": // user is approved by pts. Check whether person exists and is approved in Ginsberg db
						if ($person) {
							if ($person->getIsTermsAgreed()) {
								// We must already have their contact info, so update status and let them see index
								$person->setStatus('approved');
								break;
							} else {
								$logger->info('In actionIndex: Person exists but terms not agreed');
								$person->setStatus('approved');
								// Set program based on eligibility group they are in
								if ($person && $program) {
									$person->setProgram($program);
								}
								// We need their contact info and terms agreement
								return $this->redirect($this->generateUrl('site_start_registration'));
								//$person = Person::find_by_uniqname($uniqname);
								//$this->render('register', array('name' => User::get_first_name(), 'model' => $person));
							}
						} else {
							// user approved by PTS but not yet in Ginsberg database
							$logger->info('actionIndex: user approved by PTS but not yet in Ginsberg database');
							
              return $this->redirect($this->generateUrl('site_start_registration'));
						}
					case 'Submitted':
						if ($person) {
							$logger->info('In actionIndex, pts_status = "Submitted"');
							// Set status to pending and then route to waiting
							$person->set_status('pending');

							return $this->render('GinsbergTransportationBundle:Site:waiting_for_pts.html.twig', array(
                'name' => $provider->get_first_name(),
                'nav' => FALSE,
              ));
						} else {
							return $this->redirect($this->generateUrl('site_start_registration'));
						}
					case 'Not Approved':
						$logger->info('In actionIndex, pts_status = "Not Approved"');
						if ($person) {
							$person->setStatus('rejected');
							return $this->render('GinsbergTransportationBundle:Site:rejected.html.twig', array(
                'name' => $provider->get_first_name(),
                'nav' => FALSE,
              ));
						} else {
							return $this->render('GinsbergTransportationBundle:Site:rejected.html.twig', array(
                'name' => $provider->get_first_name(),
                'nav' => FALSE,
              ));
						}
          case 'Expired':
            if ($person) {
							$logger->info('In actionIndex, pts_status = ' . $pts_json->mvr_status);
							// Set status to pending and then route to waiting
							$person->set_status('expired');

							return $this->render('GinsbergTransportationBundle:Site:expired.html.twig', array(
                'name' => $provider->get_first_name(),
                'nav' => FALSE,
              ));
						} else {
							return $this->render('GinsbergTransportationBundle:Site:expired.html.twig', array(
                'name' => $provider->get_first_name(),
                'nav' => FALSE,
              ));
						}
				}


				// User is approved both by PTS and in the Ginsberg db, so let them proceed

				// Is the site open? If not, show closed page. If yes, show the index page.
				$site = $em->getRepository('GinsbergTransportationBundle:Installation')->find(1);
				$isOpen = $site->getIsOpen();
        // For testing
        //$is_open = TRUE;
        if (!$isOpen) {
					$open_for_res = $site->getReservationsOpen();
					$cars_available = $site->getCarsAvailable();
					return $this->render('GinsbergTransportationBundle:Site:closed.html.twig', array(
            'open_for_reservations' => $open_for_res,
            'cars_available' => $cars_available,
            'nav' => FALSE,
          ));
					//$this->redirect(array('site/closed'));
				} else {
					
          // Alert user if they have a ticket
          
					$tickets = $em->getRepository('GinsbergTransportationBundle:Ticket')->findTicketsForPerson($person);
          // FOR TESTING
          //$tickets = FALSE;
          
          $now=date("Y-m-d H:i:s");
					// Find upcoming trips for current user
					$upcomingTripsForPerson = $em->getRepository('GinsbergTransportationBundle:Reservation')->findUpcomingTripsByPerson($now, $person);

					return array(
            'nav' => TRUE,
						'name'=>$person->getFirstName(),
						'tickets'=>$tickets,
						'upcomingTripsForPerson'=>$upcomingTripsForPerson,
					);
				}
			}
		}
	}

  /**
   * Displays a form for a User to register.
   *
   * @Route("/register", name="site_start_registration")
   * @Method("GET")
   * @Template("GinsbergTransportationBundle:Site:register.html.twig")
   */
  public function initiateRegistrationAction()
  {
    $logger = $this->get('logger');
    $logger->info('In initiateRegistrationAction()');
    $em = $this->getDoctrine()->getManager();
    $provider = $this->get('user_provider');
    
// User::is_eligible will either return the name of the first eligibility group found
		// for the user, or false. Save the name of the $ldapGroup for later.
		$ldapGroup = $provider->is_eligible();

		// If the user is not eligible, (that is, they are not in a subgroup of the group
		// 'ginsberg transpo eligible'), send them to the 'ineligible' view.
		$logger->info('In SiteController::initiateRegistrationAction(). $ldapGroup: ' . $ldapGroup);
    if ($ldapGroup == FALSE) {
			return $this->render('GinsbergTransportationBundle:Site:ineligible.html.twig', array(
				'nav' => FALSE,
        'name' => $provider->get_first_name(),
			));
		}
    
    // User is eligible, so either find existing Person or create one,
    // then set values like Program where appropriate.
    $uniqname = $provider->get_uniqname();

    $mvr_status = $provider->get_pts_status_by_uniqname($uniqname);
    //$mvr_status = 'Approved'; // ***FOR TESTING***
    $logger->info('mvr_status = ' . $mvr_status);

    $program = $em->getRepository('GinsbergTransportationBundle:Program')->findByEligibilityGroup($ldapGroup);
    if (is_array($program)) {
      if (array_key_exists(0, $program)) {
        $program = $program[0];
      }
    }
    
    $logger->info('User\'s program = ' . $program);

    // Check whether user is already in database
    //$user_status = Person::get_status_by_uniqname(User::get_uniqname());
    $personRepository = $em->getRepository('GinsbergTransportationBundle:Person');
    $person = $personRepository->findByUniqname($uniqname);
    if (is_array($person)) {
      if (count($person) == 1) {
        $person = $person[0];
      }
    }
    if ($person) {
      $status = $personRepository->convertPtsStatusToGcStatus($mvr_status);
      $status = trim($status);
      $person->setStatus($status);
      if ($status == 'approved') {
        $person->setDateApproved(new \DateTime());
      }
      if ($program && !$person->getProgram()) {
        $person->setProgram($program);
      }
      $logger->info('In registerAction, person->status = ' . $person->getStatus());
    } else {
      $logger->info('No person, so creating new');
      $person = new Person();
      $person->setFirstName($provider->get_first_name());
      $person->setLastName($provider->get_last_name());
      $person->setUniqname($uniqname);
      if ($program) {
        $person->setProgram($program);
      }
      $status = $personRepository->convertPtsStatusToGcStatus($mvr_status);
      $status = trim($status);
      $person->setStatus($status);
      $logger->info('In SiteController::initiateRegistrationAction(). $status: ' . $status);
      if ($status == 'approved') {
        $person->setDateApproved(new \DateTime());
      }
      $em->persist($person);
      $em->flush();
    }
    
    // Person either fetched or created, so now display register form
    $registerForm = $this->createRegisterForm($person);

    return array(
        'nav' => FALSE,
        'entity' => $person,
        'register_form' => $registerForm->createView(),
    );
    
  }
  
  
  /**
   * Creates a form to register a Person entity.
   *
   * @param Person $entity The entity
   *
   * @return \Symfony\Component\Form\Form The form
   */
  private function createRegisterForm(Person $entity)
  {
    $logger = $this->get('logger');
    $logger->info('In createRegisterForm(). Id of entity = ' . $entity->getId());
      $form = $this->createForm(new PersonType(), $entity, array(
          'validation_groups' => array('registration'),
          'action' => $this->generateUrl('site_register', array('id' => $entity->getId())),
          'method' => 'POST',
      ));

      $form->add('submit', 'submit', array('label' => 'Register'));

      return $form;
  }

	/**
   * Registers users who are not yet in the Ginsberg Transportation System.
   *
   * @Route("/register/{id}", name="site_register")
   * @Method("POST")
   * @Template("GinsbergTransportationBundle:Site:register.html.twig")
   */
	public function registerAction(Request $request, $id)
	{
    $logger = $this->get('logger');
		$logger->info('In registerAction. $id = ' . $id);
		$em = $this->getDoctrine()->getManager();
    $entity = $em->getRepository('GinsbergTransportationBundle:Person')->find($id);
    $logger->info('entity->getFirstName() = ' . $entity->getFirstName());
    
    $provider = $this->get('user_provider');
    if (!$entity) {
      throw $this->createNotFoundException('Unable to find Person in database.');
    }
    
    $registerForm = $this->createRegisterForm($entity);
    $registerForm->handleRequest($request);

    // If this is a form submission, process
    if ($registerForm->isValid()) {
      $logger->info('register_form submitted and is valid');
      
      $em->flush();
      
      // Figure out where to redirect the person, depending on their status
      if ($entity->getStatus() == 'pending') {
        //$this->redirect(array('site/pending/'));
        return $this->render('GinsbergTransportationBundle:Site:waiting_for_pts.html.twig', array('name' => $entity->getFirstName(), 'entity' => $entity));
      } elseif ($entity->getStatus() == 'approved' && $entity->getIsTermsAgreed() == true) {
        $logger->info('Approved and terms_agreed');
        // Yes, this person is approved and has agreed to terms, so send them on
        return $this->redirect($this->generateUrl('site'));
    
      } else {
        $logger->info('Somehow got here');
        return $this->render('GinsbergTransportationBundle:Site:problem.html.twig', array(
          'name' => $provider->get_first_name(),
          'nav' => FALSE,
        ));
      }
      
    }
    
    return array(
      'nav' => FALSE,
      'entity' => $entity,
      'register_form' => $registerForm->createView(),
    );
  }
	

	/**
	 * View for pending users
	*/
	public function actionPending()
	{
	  // Make sure the user is actually in pending status
    $em = $this->getDoctrine()->getManager();
    $personRepository = $em->getRepository('GinsbergTransportationBundle:Person');
    $person = $personRepository->find($id);
    
	  $user_status = Person::get_status_by_uniqname(User::get_uniqname());
	  if ($user_status != 'pending') {
	    return $this->render('GinsbergTransportationBundle:Site:problem.html.twig', array(
        'name' => $provider->get_first_name(),
        'nav' => FALSE,
      ));
    }

		$this->render('pending',array(
		  'name'=>User::get_first_name(),
		  'date_created'=>Person::find_by_uniqname(User::get_uniqname())->date_created,
      'nav' => FALSE,
		));
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
      $logger->info('in SiteController::createCreateForm');
        $form = $this->createForm(new ReservationType(), $entity, array(
            'em' => $this->getDoctrine()->getManager(),
            'action' => $this->generateUrl('site_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create',
          'attr' => array(
            'class' => 'site_submit',
          )
        ));
        
        //$logger->info('action = ' . $this->generateUrl('site_create'));
        return $form;
    }

    /**
     * Displays a form to a regular user to create a new Reservation entity.
     *
     * @Route("/new", name="site_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
      $logger = $this->get('logger');
      $logger->info('in SiteController::newAction');
      $em = $this->getDoctrine()->getManager();
      $provider = $this->get('user_provider');

      // Ensure that user is eligible
      $ldapGroup = $provider->is_eligible();
      if ($ldapGroup == FALSE) {
        return $this->render('GinsbergTransportationBundle:Site:ineligible.html.twig', array(
          'name' => $provider->get_first_name(),
        ));
      }

      $uniqname = $provider->get_uniqname();
      $person = $em->getRepository('GinsbergTransportationBundle:Person')->findByUniqname($uniqname);
      if (is_array($person)) {
        $person = $person[0];
      }
      $program = $person->getProgram();
      $logger->info('In SiteController::newAction(). uniqname = ' . $person->getUniqname() . ' program: ' . $person->getProgram()->getName());
    
      $entity = new Reservation();
      $entity->setPerson($person);
      $entity->setProgram($program);
      $entity->setCreated(new \DateTime());
      $form   = $this->createCreateForm($entity);

      return array(
          'entity' => $entity,
          'form'   => $form->createView(),
          'nav' => TRUE,
      );
    }


	/**
   * Creates a new Reservation entity.
   *
   * @Route("/", name="site_create")
   * @Method("POST")
   * @Template("GinsbergTransportationBundle:Site:new.html.twig")
   */
	public function createAction(Request $request)
	{
    $logger = $this->get('logger');
    $logger->info("In SiteController::createAction()");
    $em = $this->getDoctrine()->getManager();
    $provider = $this->get('user_provider');
    
    // Ensure that user is eligible
    $ldapGroup = $provider->is_eligible();
    if ($ldapGroup == FALSE) {
			return $this->render('GinsbergTransportationBundle:Site:ineligible.html.twig', array(
				'name' => $provider->get_first_name(),
			));
    }
    
	  $uniqname = $provider->get_uniqname();
    $person = $em->getRepository('GinsbergTransportationBundle:Person')->findByUniqname($uniqname);
		if (is_array($person)) {
      $person = $person[0];
    }
    $program = $person->getProgram();
    $logger->info('In SiteController::createAction(). uniqname = ' . $person->getUniqname() . ' program: ' . $person->getProgram()->getName());
    
    $entity = new Reservation();
		$entity->setPerson($person);
    $entity->setProgram($program);
    //var_dump($entity->getPerson());
    $form = $this->createCreateForm($entity);
    $form->handleRequest($request);
		
	  if($form->isValid()){
      $logger->info('In SiteController::createAction(), form is valid.');
      // TODO
			// Use the program_id to determine whether to set the scenario to "pc_reservation" or "non_pc_reservation".
			// This will determine with it is the "destination_id" select list field that is required (for Project Community), or
			// the "destination" textfield (everybody else). Program_id 2 == Project Community
			/*if (isset($_POST['Reservation']['program_id'])) {
				if ($_POST['Reservation']['program_id'] == '2') {
					$entity->scenario = 'pc_reservation';
				} else {
					$entity->scenario = 'non_pc_reservation';
				}
			}*/

		  // Create arrays to hold successful and unsuccessful vehicle 
      // assignments
      $successfulReservations = array();
      $failedReservations = array();

      // Is this a repeating reservation?
      $isRepeatingReservation = $form->get('isRepeating')->getData();
      $logger->info("isRepeatingReservation = $isRepeatingReservation");
			
      // This is a regular user, so they can't request a particular vehicle
      $vehicleRequested = FALSE;
      
			// If this is a repeating reservation, create the Series and get the
      // series id to set in the Reservation entity.
      if ($isRepeatingReservation)
      {
        $seriesEntity = new Series();
        $em->persist($seriesEntity);
        $em->flush();
        $entity->setSeries($seriesEntity);
      }
			// Save the Reservation before attempting to assign it to a vehicle.
			$em->persist($entity);
      $logger->info('Just persisted reservation entity prior to assigning vehicle. person Id: ');
      //var_dump($entity->getPerson());
    
      $em->flush(); 
      
      // Get the ReservationRepository in order to assign the vehicle
      $reservationRepository = $em->getRepository('GinsbergTransportationBundle:Reservation');
      $entity = $reservationRepository->assignReservationToVehicle($entity, $vehicleRequested);

      $em->flush();
      
      // Check if this is a repeating reservation.
      if($isRepeatingReservation) {
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

        // The "repeatsUntil" field in the Reservation form is not mapped 
        // to the database or the entity, so we get it from the $form
        $repeatsUntil = $form->get('repeatsUntil')->getData();
        $reservationEndTime = clone($entity->getEnd());
        $reservationEndTime = date('H:i', $reservationEndTime->getTimestamp());
        $logger->info('repeatsUntil starts out as ' . date('Y-m-d H:i:s', $repeatsUntil->getTimestamp()));
        list($repeatHour, $repeatMinute) = explode(':', $reservationEndTime);
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
        } // End while loop that creates repeating reservations
      } // End if that handles repeating reservations
      
      // Reservation(s) created, now direct to appropriate view
      if ($isRepeatingReservation) {
        return $this->render('GinsbergTransportationBundle:Site:list_created_repeating.html.twig', array(
            'nav' => TRUE,
            'successes' => count($successfulReservations), 
            'failures' => count($failedReservations),
            'entities' => $entity->getSeries()->getReservations()));
      } else {
        // It's just a single reservation. Redirect to the Show template  
        // with a success or failure Flash message
        //$logger->info('This is a single reservation with Id ' . $entity->getId());
        if ($entity->getVehicle()) {
          $id = $entity->getId();
          $vehicleName = $entity->getVehicle()->getName();
          $this->get('session')->getFlashBag()->add(
              'sucess',
              "Success! Reservation $id with vehicle $vehicleName has been created."
          );
          return $this->redirect($this->generateUrl('site_show', array('id' => $entity->getId())));
        } else {
          $this->get('session')->getFlashBag()->add(
              'failure',
              'Sorry! No vehicle is available at the requested time.'
          );
          return $this->redirect($this->generateUrl('site_show', array('id' => $entity->getId())));
        }
      }
    } // End $form->isValid()

    return array(
        'nav' => TRUE,
        'entity' => $entity,
        'form'   => $form->createView(),
    );
	}

	/**
   * Finds and displays a Reservation entity.
   *
   * @Route("/show/{id}", name="site_show")
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

        //$deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'nav' => TRUE,
            //'delete_form' => $deleteForm->createView(),
        );
    }

	/**
   * Display a list of past reservations.
   *
   * @Route("/past", name="site_past")
   * @Method("GET")
   * @Template("GinsbergTransportationBundle:Site:past.html.twig")
   */
	public function pastAction()
 	{
    $provider = $this->get('user_provider');
    $uniqname = $provider->get_uniqname();
    $em = $this->getDoctrine()->getManager();
    $person = $em->getRepository('GinsbergTransportationBundle:Person')->findByUniqname($uniqname);
		if (is_array($person)) {
      $person = $person[0];
    }
    
    $pastTripsForPerson = $em->getRepository('GinsbergTransportationBundle:Reservation')->findPastTripsByPerson($person);
    

 		return array(
        'pastTripsForPerson' => $pastTripsForPerson,
        'nav' => TRUE,
    );
 	}
}
