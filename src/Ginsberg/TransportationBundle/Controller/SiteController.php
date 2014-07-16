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
use Ginsberg\TransportationBundle\Entity\Program;
use Ginsberg\TransportationBundle\Security\User\UserProvider;
use Ginsberg\TransportationBundle\Form\ReservationType;
use Ginsberg\TransportationBundle\Form\PersonType;
use Ginsberg\TransportationBundle\Services\ProgramService;
use Ginsberg\TransportationBundle\Services\PersonService;

/**
 * Controller for public-facing area of Transportation website
 * 
 * @Route("/")
 */
class SiteController extends Controller
{
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		);
	}


	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
		);
	}


	/**
	 * Specifies the access control rules.
	 *
	 * This method is used by the 'accessControl' filter.
	 * This method handles access, but actual routing is handled by actionIndex
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow',
			  // allow all authenticated users to perform basic actions
			  // Actual routing is handled by index.
			  // TODO move routing to a filter -- but
				'actions'=>array('index', 'create', 'register', 'pending', 'rejected', 'view',
												 'past', 'delete', 'closed', 'ineligible', 'not_in_mvr',
												 'problem', 'ginsberg_not_delegate'),
      	'expression'=>'User::is_authenticated()',
			),

			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('create', 'view', 'past', 'closed'),
    		'expression'=>'User::is_approved()',
			),

			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

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
		// for the user, or false. Save the name of the $ldap_group for later.
		$ldap_group = $provider->is_eligible();
    $logger->info("ldap group = $ldap_group");

		// If the user is not eligible, (that is, they are not in a subgroup of the group
		// 'ginsberg transpo eligible'), send them to the 'ineligible' view.
		if ($ldap_group == FALSE) {
			return $this->render('GinsbergTransportationBundle:Site:ineligible.html.twig', array(
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
					$this->render('problem', array('name' => $provider->get_first_name(),));
					//$this->redirect(array('site/problem/'));
					break;
				case 405: // Ginsberg not delegate
					$done = true;
					$this->render('ginsberg_not_delegate', array('name' => $provider->get_first_name(),));
					//$this->redirect(array('site/ginsberg_not_delegate/'));
					break;
				case 404: // Student not in MVR database
					$done = true;
					$this->render('not_in_mvr', array('name' => $provider->get_first_name(),));
					break;
				case 401: // Unauthorized
					$done = true;
					$this->render('problem', array('name' => $provider->get_first_name(),));
					//$this->redirect(array('site/problem/'));
					break;
				case 400: // Bad request, such as no uniqname
					$done = true;
					$this->render('problem', array('name' => $provider->get_first_name(),));
					//$this->redirect(array('site/problem/'));
					break;
			}

			// If we got a 200 code, continue processing. Otherwise, we're done.
			if (!$done) {
        $person = $em->getRepository('GinsbergTransportationBundle:Person')->findByUniqname($uniqname);
				if (is_array($person)) {
          $person = $person[0];
        }
				// If the person already set their program manually, don't change it.
				// Otherwise, set it based on the eligibility group they are in.
        // We know the person has a program set if isTermsAgreed is true, since
        // they had to set a program on the registration form.
				$program = $em->getRepository('GinsbergTransportationBundle:Program')->findByEligibilityGroup($ldap_group);
				if (is_array($program)) {
          $program = $program[0];
        }
        $logger->info('User\'s program = ' . $program->getName());
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
								break;
							}
						} else {
							// user approved by PTS but not yet in Ginsberg database
							$logger->info('actionIndex: user approved by PTS but not yet in Ginsberg database');
							return $this->redirect('GinsbergTransportationBundle:Site:register');
							break;
						}
					case 'Submitted':
						if ($person) {
							$logger->info('In actionIndex, pts_status = "Submitted"');
							// Set status to pending and then route to waiting
							$person->set_status('pending');

							$this->render('waiting_for_pts', array('name' => $person->first_name));
							break;
						} else {
							$this->redirect(array('site/register'));
							break;
						}
					case 'Not Approved':
						$logger->info('In actionIndex, pts_status = "Not Approved"');
						if ($person) {
							$person->set_status('rejected');
							$this->render('rejected', array('name' => $person->first_name));
						} else {
							$this->render('rejected', array('name' => User::get_first_name(),));
						}
				}


				// User is approved both by PTS and in the Ginsberg db, so let them proceed

				// Is the site open? If not, show closed page. If yes, show the index page.
				$site_open = Installation::is_open(1);
				if (!$site_open) {
					$site = Installation::model()->findByPk(1);
					$open_for_res = $site->reservations_open;
					$cars_available = $site->cars_available;
					$this->render('closed', array(
						'model'=>$site,
					));
					//$this->redirect(array('site/closed'));
				} else {
					$now=date("Y-m-d H:i:s");
					$ticket = Ticket::has_tickets($uniqname);

					// Find upcoming trips for current user
					$criteria=new CDbCriteria;
					$criteria->condition='start >= :now AND driver_uniqname = "' . $uniqname . '"';
					$criteria->params=array(':now'=>$now);
					$criteria->order = 'start ASC';
					$upcoming_trips = new CActiveDataProvider('Reservation', array(
						'criteria'=>$criteria,
						'pagination'=>array(
								'pageSize'=>100,
						),
					));

					$this->render('index',array(
						'name'=>User::get_first_name(),
						'ticket'=>$ticket,
						'upcoming_trips'=>$upcoming_trips,
					));
				}
			}
		}
	}

  /**
   * Displays a form for a User to register.
   *
   * @Route("/register", name="site_start_registration")
   * @Method("GET")
   * @Template()
   */
  public function initiateRegistrationAction()
  {
    $logger = $this->get('logger');
    $logger->info('In registerAction');
    $em = $this->getDoctrine()->getManager();
    $provider = $this->get('user_provider');
    
// User::is_eligible will either return the name of the first eligibility group found
		// for the user, or false. Save the name of the $ldap_group for later.
		$ldap_group = $provider->is_eligible();

		// If the user is not eligible, (that is, they are not in a subgroup of the group
		// 'ginsberg transpo eligible'), send them to the 'ineligible' view.
		if ($ldap_group == FALSE) {
			return $this->render('GinsbergTransportationBundle:Site:ineligible.html.twig', array(
				'name' => $provider->get_first_name(),
			));
		}
    
    // User is eligible, so either find existing Person or create one,
    // then set values like Program where appropriate.
    $uniqname = $provider->get_uniqname();

    $mvr_status = $provider->get_pts_status_by_uniqname($uniqname);
    //$mvr_status = 'Approved'; // ***FOR TESTING***
    $logger->info('mvr_status = ' . $mvr_status);

    $program = $em->getRepository('GinsbergTransportationBundle:Program')->findByEligibilityGroup($ldap_group);
    if (is_array($program)) {
      $program = $program[0];
    }
    $logger->info('User\'s program = ' . $program);

    // Check whether user is already in database
    //$user_status = Person::get_status_by_uniqname(User::get_uniqname());
    $person = $em->getRepository('GinsbergTransportationBundle:Person')->findByUniqname($uniqname);
    if (is_array($person)) {
      $person = $person[0];
    }
    if ($person) {
      $personService = $this->get('person_service');
      $person->setStatus($personService->convert_pts_status_to_gc_status($mvr_status));
      if ($program && !$person->getProgram()) {
        $person->setProgram($program);
      }
      $logger->info('In registerAction, person->status = ' . $person->getStatus());
    } else {
      $logger->info('No person, so creating new');
      $person = new Person('register');
      $person->setFirstName(User::get_first_name());
      $person->setLastName(User::get_last_name());
      if ($program) {
        $person->setProgram($program);
      }
      $person->getStatus(Person::convert_pts_status_to_gc_status($mvr_status));
      if ($mvr_status == 'Not Approved') {
        $this->redirect(array('site/rejected'));
      }
      $em->persist($person);
      $em->flush();
    }
    
    // Person either fetched or created, so now display register form
    $registerForm = $this->createRegisterForm($person);

    return array(
        'entity'      => $person,
        'register_form'   => $registerForm->createView(),
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
          'action' => $this->generateUrl('site_register', array('id' => $entity->getId())),
          'method' => 'PUT',
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
    $logger->info('entity->getName() = ' . $entity->getName());
    
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
        $this->render('terms', array('name' => $entity->first_name, 'entity' => $entity));
      }
      
    }
    
    return array(
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
	  $user_status = Person::get_status_by_uniqname(User::get_uniqname());
	  if ($user_status != 'pending'):
	    $this->redirect(array('/'));
  	endif;

		$this->render('pending',array(
		  'name'=>User::get_first_name(),
		  'date_created'=>Person::find_by_uniqname(User::get_uniqname())->date_created,

		));
	}


	/**
	 * View for rejected users
	*/
	public function actionRejected()
	{
	  $person = Person::find_by_uniqname(User::get_uniqname());

	  // Make sure the user has actually been rejected.
	  $user_status = Person::get_status_by_uniqname(User::get_uniqname());
	  if ($user_status != 'rejected'):
	    $this->redirect(array('/'));
  	endif;

		$this->render('rejected',array(
		  'name'=>User::get_first_name(),
		  'reason'=>$person->private_reason,
		));
	}

	/**
	 * View when site is closed
	 */
	public function actionClosed()
	{
		$this->render('closed',array());
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
    if($error=Yii::app()->errorHandler->error)
    {
    	if(Yii::app()->request->isAjaxRequest)
    		echo $error['message'];
    	else
        $this->render('error', $error);
    }
	}


	/**
    * Returns the timestamp of the given date + one week
    * Aka: adds a week to a date
    * 2pm Monday June 22 returns 2pm Monday June 29.
	  */
	private function add_a_week($timestamp)
	{
	  return Assert(False);
	}


	/**
	 * Create a reservation
	 */
	public function actionCreate()
	{
	  $uniqname = User::get_uniqname();
		$ldap_group = User::is_eligible();
		$users_program = Program::get_program_name_by_ldap_group($ldap_group);
	  $model = new Reservation;
		if ($users_program) {
			$model->program = $users_program;
		}
	  if(isset($_POST['Reservation'])){
			// Use the program_id to determine whether to set the scenario to "pc_reservation" or "non_pc_reservation".
			// This will determine with it is the "destination_id" select list field that is required (for Project Community), or
			// the "destination" textfield (everybody else). Program_id 2 == Project Community
			if (isset($_POST['Reservation']['program_id'])) {
				if ($_POST['Reservation']['program_id'] == '2') {
					$model->scenario = 'pc_reservation';
				} else {
					$model->scenario = 'non_pc_reservation';
				}
			}

		  $model->attributes=$_POST['Reservation'];
			$model->driver_uniqname = User::get_uniqname();

			// Convert text dates like "tomorrow" into a datetime for saving in MySQL
		  $start_datetime_str = date('Y-m-d H:i:s', strtotime($model->start));
		  $model->start = $start_datetime_str;
		  $end_datetime_str = date('Y-m-d H:i:s', strtotime($model->end));
      $model->end = $end_datetime_str;

			$repeating = False;
			// If this is a repeating reservation, create the series and get the series_id to save in the Reservation $model
			if(array_key_exists('repeating', $_POST)) {
				if($_POST['repeating'] === 'on'){
					$series_model = new Series;
					$series_model->save();
					$series_id = $series_model->id;
					$model->series_id = $series_id;
					$repeating = True;
				}
			}
			// If the reservation can be successfully saved, attempt to assign it to
			// a vehicle.
			if($model->save()){
			  $model->assign_reservation_to_vehicle();

        // Check if this is a repeating reservation.
    	  if(array_key_exists('repeating', $_POST)) {
    	    if ($_POST['repeating'] === 'on'){
    	      $logger->info('testing', 'info', 'system.debug');

      	    $repeating_reservations_created = 0; // counter
        	  $no_vehicle_available = Array(); // to keep track of reservations we can't make
        	  //$interval = new DateInterval("P7D"); // DateInterval for 7 days


        	  // calculate final repeat date.
      	    $repeat_until = strtotime($_POST['until'] . ' 22:00:00');

      	    // translate the start and end times into datetime objects
      	    $start_datetime = strtotime($start_datetime_str);
      	    $end_datetime = strtotime($end_datetime_str);

						$start_datetime = Format::get_repeat_interval($start_datetime);
						$end_datetime = Format::get_repeat_interval($end_datetime);

      	    while($start_datetime < $repeat_until) {
      	      // create reservation for new date
      	      $reservation = new Reservation;
							// Use the program_id to determine whether to set the scenario to "pc_reservation" or "non_pc_reservation".
							// This will determine with it is the "destination_id" select list field that is required (for Project Community), or
							// the "destination" textfield (everybody else). Program_id 2 == Project Community
							if (isset($_POST['Reservation']['program_id'])) {
								if ($_POST['Reservation']['program_id'] == '2') {
									$reservation->scenario = 'pc_reservation';
								} else {
									$reservation->scenario = 'non_pc_reservation';
								}
							}
      	      $reservation->attributes=$_POST['Reservation'];
							$reservation->series_id = $series_id;
      	      $reservation->start = date('Y-m-d H:i:s', $start_datetime);
      	      $reservation->end = date('Y-m-d H:i:s', $end_datetime);
      	      $reservation->driver_uniqname = User::get_uniqname();
      	      $reservation->save();
      	      $logger->info(serialize($reservation->getErrors()));

      	      $logger->info("Reservation start time: " . $reservation->start);

      	      // save & find vehicle
      	      // if no vehicle available, add to list
      	      if(!$reservation->assign_reservation_to_vehicle()) {
      	        array_push($no_vehicle_available, $reservation);
							}

      	      $repeating_reservations_created += 1; // increment counter

							$start_datetime = Format::get_repeat_interval($start_datetime);
							$end_datetime = Format::get_repeat_interval($end_datetime);
						}

					}
				}
				if($repeating) {
					/*
					$series_data = new CActiveDataProvider('Reservation', array(
						'criteria'=>array('condition'=>'series_id='.$series_id,
															'order'=>'start ASC',),
						'pagination'=>array(
								'pageSize'=>100,
						),
					));
					*/
					$series_data = Reservation::get_reservations_in_series($series_id);
					$this->render('view_repeating',array(
						'series_id'=>$series_id,
						'series_data'=>$series_data,
					));
				} else {
					$this->redirect(array('site/view/' . $model->id));
				}

			}

		}

	  $this->render('create',array(
	    'model'=>$model,
	    //'repeating'=>$repeating,
			//'repeating_reservations_created'=> $repeating_reservations_created,
			//'no_vehicle_available' => $no_vehicle_available,
    ));
	}

	/*
	 * Handle ineligible users
	 */
	public function actionIneligible()
	{
		// Make sure the user is actually ineligible
		// Originally I had this checking the Person's status, but I don't think we want to use a set status to track elegibility
		if (!User::is_eligible()) {
			$this->render('ineligible', array(
				'name' => User::get_first_name(),
			));
		}
	}




	/**
	 * Delete a future reservation. Delete button is only displayed in view.php if
	 * reservation is in the future.
	 * Actually, couldn't get Delete button to redirect properly at the end of this function.
	 * The index page would display, but the URL would still be .../delete/78, so if
	 * someone refreshed the page, they would get a blank screen (since reservation 78 wouldn't exit anymore).
	 * So for now, commented Delete link out of view.php, and this function never gets called.
	 */
	public function actionDelete($id) {
		$model = $this->loadReservation($id);
		$model->delete();
		$this->redirect(array('/site/index/'));
		//$this->redirect(Yii::app()->homeUrl);
		//Yii::app()->request->redirect(Yii::app()->createUrl(array('/site/index')));
	}

	/**
	 * Displays a reservation.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$this->render('view',array(
			'model'=>$this->loadReservation($id),
		));
	}


	public function actionPast()
 	{
     $now=date("Y-m-d H:i:s");
 	   $criteria=new CDbCriteria;
     $criteria->condition='end < :now AND driver_uniqname = :uniqname';
     $criteria->params=array(':now'=>$now, ':uniqname'=>User::get_uniqname());
//   $results = Reservation::model()->find($criteria);

     $dataProvider = new CActiveDataProvider('Reservation', array(
       'criteria'=>$criteria,
     ));


 		$this->render('past',array(
 			'dataProvider'=>$dataProvider,
 		));
 	}



	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadReservation($id)
	{
		$model=Reservation::model()->findByPk((int)$id);

		// Make sure the reservation exists
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');

		// Make sure the reservation belongs to the current user TODO
		//if ($model->driver_uniqname != User::get_uniqname())
		//  throw new CHttpException(500,'This reservation does not belong to you.');

		return $model;
	}


	/**
	 * Displays the login page
	 */
	public function actionLogin()
	{
		$model=new LoginForm;

		// if it is ajax validation request
		if(isset($_POST['ajax']) && $_POST['ajax']==='login-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		// collect user input data
		if(isset($_POST['LoginForm']))
		{
			$model->attributes=$_POST['LoginForm'];
			// validate user input and redirect to the previous page if valid
			if($model->validate() && $model->login())
				$this->redirect(Yii::app()->user->returnUrl);
		}
		// display the login form
		$this->render('login',array('model'=>$model));
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		Yii::app()->user->logout();
		$this->redirect(Yii::app()->homeUrl);
	}
}


