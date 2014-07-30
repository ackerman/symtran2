<?php

namespace Ginsberg\TransportationBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Ginsberg\TransportationBundle\Entity\Person;
use Ginsberg\TransportationBundle\Form\PersonType;

/**
 * Person controller.
 *
 * @Route("/person")
 */
class PersonController extends Controller
{

    /**
     * Lists all Person entities with status pending. Needs to include a Search form.
     *
     * @Route("/", name="person")
     * @Method("GET")
     * @Template()
     */
    public function indexAction($entities = NULL)
    {
      $logger = $this->get('logger');
      $logger->info('In PersonController::indexAction()');
      if ($entities == NULL) {
        $em = $this->getDoctrine()->getManager();
        
        //$entities = $em->getRepository('GinsbergTransportationBundle:Person')->findByPendingSortedByCreated('pending');
        $entities = $em->getRepository('GinsbergTransportationBundle:Person')->findAll();
      }
        
      return array(
          'entities' => $entities,
      );
    }
    
    /**
     * Searches the Person table.
     *
     * @Route("/search", name="person_search")
     * @Method("POST")
     * @Template("GinsbergTransportationBundle:Person:index.html.twig")
     */
    public function searchAction(Request $request)
    {
        $entity = new Person();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        
        
        
        if ($form->isValid()) {
          $firstName = $form->get('firstName')->getData();
          $lastName = $form->get('lastName')->getData();
          $uniqname = $form->get('uniqname')->getData();
          $program = $form->get('program')->getData();
          $em = $this->getDoctrine()->getManager();

          $entities = $em->getRepository('GinsbergTransportationBundle:Person')->findBySearchCriteria($firstName, $lastName, $uniqname, $program);

          // Can't get PrePersist to work, so setting created here
          //$entity->setCreated(new \DateTime());

          //$em->persist($entity);
          //$em->flush();
          
          $response = $this->forward('GinsbergTransportationBundle:Person:index', array(
              'entities' => $entities,
          ));
          
          return $response;
          return array(
            'entities' => $entities,
          );
          //return $this->redirect($this->generateUrl('person_search'));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to search the Person table.
    *
    * @param Person $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createSearchForm(Person $entity)
    {
        $form = $this->createForm(new PersonType(), $entity, array(
            'action' => $this->generateUrl('person_search'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Search'));

        return $form;
    }

    /**
     * Displays a form to search the Person table.
     *
     * @Route("/search", name="person_search_criteria")
     * @Method("GET")
     * @Template()
     */
    public function searchCriteriaAction()
    {
        $entity = new Person();
        $form   = $this->createSearchForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }
    
    /**
     * Creates a new Person entity.
     *
     * @Route("/", name="person_create")
     * @Method("POST")
     * @Template("GinsbergTransportationBundle:Person:new.html.twig")
     */
    public function createAction(Request $request)
    {
      $logger = $this->get('logger');
      $logger->info('Just entered PersonController::createAction');
        
        $entity = new Person();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        $logger->info('in PersonController::createAction after handleRequest. isValid = ' . $form->isValid());
          

        if ($form->isValid()) {
          $logger->info('in PersonController::createAction, form is valid');
            $em = $this->getDoctrine()->getManager();
            
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('person_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a Person entity.
    *
    * @param Person $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Person $entity)
    {
      $logger = $this->get('logger');
      $logger->info('Just entered PersonController::createCreateForm()');
       
        $form = $this->createForm(new PersonType(), $entity, array(
            'validation_groups' => array('Default'),
            'action' => $this->generateUrl('person_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));
        $logger->info('Just created the form in PersonController::createCreateForm()');
      
        return $form;
    }

    /**
     * Displays a form to create a new Person entity.
     *
     * @Route("/new", name="person_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
      $logger = $this->get('logger');
      $logger->info('In PersonController::newAction()');
        $entity = new Person();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Person entity, including lists of future and past reservations.
     *
     * @Route("/{id}", name="person_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        
        $now = new \DateTime();
        $entity = $em->getRepository('GinsbergTransportationBundle:Person')->find($id);
        $reservationRepository = $em->getRepository('GinsbergTransportationBundle:Reservation');
        $upcoming = $reservationRepository->findUpcomingTripsByPerson($now, $entity);
        $past = $reservationRepository->findPastTripsByPerson($entity);
        $tickets = $reservationRepository->findTicketsForReservationByPerson($entity);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Person entity.');
        }
        
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'upcoming_trips' => $upcoming,
            'past_trips' => $past,
            'tickets' => $tickets,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Person entity.
     *
     * @Route("/{id}/edit", name="person_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('GinsbergTransportationBundle:Person')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Person entity.');
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
    * Creates a form to edit a Person entity.
    *
    * @param Person $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Person $entity)
    {
        $form = $this->createForm(new PersonType(), $entity, array(
            'action' => $this->generateUrl('person_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Person entity.
     *
     * @Route("/{id}", name="person_update")
     * @Method("PUT")
     * @Template("GinsbergTransportationBundle:Person:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('GinsbergTransportationBundle:Person')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Person entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('person_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Person entity.
     *
     * @Route("/{id}", name="person_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('GinsbergTransportationBundle:Person')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Person entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('person'));
    }

    /**
     * Creates a form to delete a Person entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('person_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }

}
