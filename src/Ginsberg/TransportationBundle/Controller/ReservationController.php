<?php

namespace Ginsberg\TransportationBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Ginsberg\TransportationBundle\Entity\Reservation;
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
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('GinsbergTransportationBundle:Reservation')->findAll();

        return array(
            'entities' => $entities,
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
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('reservation_show', array('id' => $entity->getId())));
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
}
