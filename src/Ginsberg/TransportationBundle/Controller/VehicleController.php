<?php

namespace Ginsberg\TransportationBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Ginsberg\TransportationBundle\Entity\Vehicle;
use Ginsberg\TransportationBundle\Form\VehicleType;

/**
 * Vehicle controller.
 *
 * @Route("/vehicle")
 */
class VehicleController extends Controller
{

    /**
     * Lists all Vehicle entities.
     *
     * @Route("/", name="vehicle")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('GinsbergTransportationBundle:Vehicle')->findAllSorted();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Vehicle entity.
     *
     * @Route("/", name="vehicle_create")
     * @Method("POST")
     * @Template("GinsbergTransportationBundle:Vehicle:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Vehicle();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('vehicle_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a Vehicle entity.
    *
    * @param Vehicle $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Vehicle $entity)
    {
        $form = $this->createForm(new VehicleType(), $entity, array(
            'action' => $this->generateUrl('vehicle_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Vehicle entity.
     *
     * @Route("/new", name="vehicle_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Vehicle();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Vehicle entity.
     *
     * @Route("/{id}", name="vehicle_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('GinsbergTransportationBundle:Vehicle')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Vehicle entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Vehicle entity.
     *
     * @Route("/{id}/edit", name="vehicle_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('GinsbergTransportationBundle:Vehicle')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Vehicle entity.');
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
    * Creates a form to edit a Vehicle entity.
    *
    * @param Vehicle $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Vehicle $entity)
    {
        $form = $this->createForm(new VehicleType(), $entity, array(
            'action' => $this->generateUrl('vehicle_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Vehicle entity.
     *
     * @Route("/{id}", name="vehicle_update")
     * @Method("PUT")
     * @Template("GinsbergTransportationBundle:Vehicle:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
      $logger = $this->get('logger');
        $em = $this->getDoctrine()->getManager();
        $vehicleRepository = $em->getRepository('GinsbergTransportationBundle:Vehicle');
        
        $isOriginallyActive = $vehicleRepository->find($id)->getIsActive();
        
        $entity = $vehicleRepository->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Vehicle entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
          $logger->info('In VehicleController::updateAction(). $entity Name: ' . $entity->getName());
          
          if ($entity->getMaintenanceStartDate() && $entity->getMaintenanceEndDate()) {
            $maintenanceStartDate = $entity->getMaintenanceStartDate();
            $maintenanceEndDate = $entity->getMaintenanceEndDate();
            $maintenanceProgram = $em->getRepository('GinsbergTransportationBundle:Program')->findByName('Maintenance');
            $reservationsForBrokenVehicle = $vehicleRepository->findReservationsForBrokenVehicle($entity, $maintenanceProgram, $maintenanceStartDate, $maintenanceEndDate);
            $logger->info('In VehicleController::updateAction(). About to call _reassignVehicle() for ' . count($reservationsForBrokenVehicle) . ' vehicles.');
            $reassignmentsAndErrors = $this->_reassignVehicles($entity, $reservationsForBrokenVehicle, $maintenanceStartDate, $maintenanceEndDate);
            $reservationsReassigned = $reassignmentsAndErrors['reservationsReassigned'];
            $reservationsNotReassigned = $reassignmentsAndErrors['reservationsNotReassigned'];
            
            // Notify the drivers whose vehicles we couldn't reassign.
            if ($reservationsNotReassigned) {
              $this->_notifyOfCancelledReservation($reservationsNotReassigned);
            }
            
            $note = $entity->getNotes();
            $entity->setNotes($note . ' Out for maintenance: ' . $maintenanceStartDate->format('Y-m-d H:i:s') . ' to ' . $maintenanceEndDate->format('Y-m-d H:i:s'));
          }
          
          // If an active vehicle is being made inactive, reassign all of its reservations
          if ($isOriginallyActive && $entity->getIsActive() == FALSE) {
            $em->persist($entity);
            $em->flush();
            
            $start = new \DateTime();
            $reassignmentsAndErrors = $vehicleRepository->makeVehicleInactive($start);
            
            $reservationsReassigned = $reassignmentsAndErrors['reservationsReassigned'];
            $reservationsNotReassigned = $reassignmentsAndErrors['reservationsNotReassigned'];
            
            // Notify the drivers whose vehicles we couldn't reassign.
            if ($reservationsNotReassigned) {
              $this->_notifyOfCancelledReservation($reservationsNotReassigned);
            }
            
          }
          
          $em->persist($entity);
          $em->flush();

          return $this->redirect($this->generateUrl('vehicle_show', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    
    /**
     * Deletes a Vehicle entity.
     *
     * @Route("/{id}", name="vehicle_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('GinsbergTransportationBundle:Vehicle')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Vehicle entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('vehicle'));
    }

    /**
     * Creates a form to delete a Vehicle entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('vehicle_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
    
    private function _notifyOfCancelledReservations($reservationsNotReassigned) 
    {
      $logger = $this->get('logger');
      $reportToAdmin = array();
      $superUsers = 'ginsberg.transportation.superusers@umich.edu';
      foreach ($reservationsNotReassigned as $reservation) {
        $driver = $reservation->getPerson();
        $uniqname = $driver->getUniqname();
        $start = $reservation->getStart()->format('Y-m-d H:i');
        $end = $reservation->getEnd()->format('Y-m-d H:i');
        $fullName = $name . ' ' . $driver->getLastName();
        $email = $driver->getUniqname() . '@umich.edu';
        $bcc = $superUsers;
        $subject = 'URGENT: Ginsberg Transportation Reservation Cancelled';
      
        $message = \Swift_Message::newInstance()
              ->setSubject($subject)
              ->setFrom('transpoinfo@umich.edu')
              ->setTo($email)
              ->setBcc($bcc)
              ->setBody(
                $this->renderView('GinsbergTransportationBundle:Vehicle:email_cancelled_reservation.html.twig', array(
                    'reservation' => $reservation,
                )), 'text/html'
              );
        $this->get('mailer')->send($message);
        $reportString = "$fullName ($uniqname) start: $start end: $end";
        $logger->info($reportString);
        array_push($reportToAdmin, $reportString);
      }               
       
      $reportBody = "The following users had reservations that could not be reassigned when a vehicle was taken out of service: <br />\n";
      foreach ($reportToAdmin as $user) {
        $reportBody .= "$user<br />\n";
      }
      $logger->info($reportBody);
      $reportSubject = "Drivers with un-reassigned reservations";
      $reportMessage = \Swift_Message::newInstance()
              ->setSubject($reportSubject)
              ->setFrom('transpoinfo@umich.edu')
              ->setTo($superUsers)
              ->setBody($reportBody, 'text/html'
              );
        $this->get('mailer')->send($reportMessage);
    }
    
    private function _reassignVehicles($vehicle, $reservationsToChange, $start, $end) 
    {
      $logger = $this->get('logger');
      $logger->info('In VehicleController::_reassignVehicles(). There are ' . count($reservationsToChange) . ' reservations to change.');
      $em = $this->getDoctrine()->getManager();
        
        // Loop through all the Reservations and remove the Vehicle from each.
        foreach ($reservationsToChange as $reservation) {
          $logger->info('in VehicleController::_reassignVehicles. Reservation: ' . $reservation->getId());
          $reservation->setVehicle(NULL);
          $em->persist($reservation);
        }
        
        $em->flush();
        // Make a dummy reservation for the given time so that we don't just 
        // reassign to the same vehicle
        $personRepository = $em->getRepository('GinsbergTransportationBundle:Person');
        $person = $personRepository->findByUniqname('ebusta');
        if (is_array($person)) {
          $person = $person[0];
        }
        $programRepository = $em->getRepository('GinsbergTransportationBundle:Program');
        $program = $programRepository->findByName('Maintenance');
        if (is_array($program)) {
          $program = $program[0];
        }
        $dummyReservation = new \Ginsberg\TransportationBundle\Entity\Reservation();
        $dummyReservation->setStart($start)->setEnd($end);
        $dummyReservation->setPerson($person);
        $dummyReservation->setSeatsRequired(1);
        $dummyReservation->setProgram($program);
        $dummyReservation->setDestinationText('Maintenance');
        $dummyReservation->setNotes('Maintenance: ' . $start->format('Y-m-d H:i') . ' - ' . $end->format('Y-m-d H:i'));
        $dummyReservation->setVehicle($vehicle);
        $dummyReservation->setCreated(new \DateTime());

        $em->persist($dummyReservation);
        $em->flush();

        // Now try to reassign each Vehicle
        $reservationRepository = $em->getRepository('GinsbergTransportationBundle:Reservation');

        $reservationsReassigned = array();
        $reservationsNotReassigned = array();
        foreach ($reservationsToChange as $reservationToChange) {
          $logger->info('reservationToChange: ' . $reservationToChange->getId());
          $reservationToChange = $reservationRepository->assignReservationToVehicle($reservationToChange);
          $em->persist($reservationToChange);
          if ($reservationToChange->getVehicle() == NULL) {
            array_push($reservationsNotReassigned, $reservationToChange);
          } else {
            array_push($reservationsReassigned, $reservationToChange);
          }
          
          $em->flush();
        }
        return array(
            'reservationsReassigned' => $reservationsReassigned,
            'reservationsNotReassigned' => $reservationsNotReassigned
        );
      }
}
