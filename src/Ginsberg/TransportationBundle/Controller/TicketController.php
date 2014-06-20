<?php

namespace Ginsberg\TransportationBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Ginsberg\TransportationBundle\Entity\Ticket;
use Ginsberg\TransportationBundle\Form\TicketType;

/**
 * Ticket controller.
 *
 * @Route("/ticket")
 */
class TicketController extends Controller
{

    /**
     * Lists all Ticket entities.
     *
     * @Route("/", name="ticket")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('GinsbergTransportationBundle:Ticket')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Ticket entity.
     *
     * @Route("/", name="ticket_create")
     * @Method("POST")
     * @Template("GinsbergTransportationBundle:Ticket:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Ticket();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('ticket_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a Ticket entity.
    *
    * @param Ticket $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Ticket $entity)
    {
        $form = $this->createForm(new TicketType(), $entity, array(
            'action' => $this->generateUrl('ticket_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Ticket entity.
     *
     * @Route("/new", name="ticket_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Ticket();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Ticket entity.
     *
     * @Route("/{id}", name="ticket_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('GinsbergTransportationBundle:Ticket')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Ticket entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Ticket entity.
     *
     * @Route("/{id}/edit", name="ticket_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('GinsbergTransportationBundle:Ticket')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Ticket entity.');
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
    * Creates a form to edit a Ticket entity.
    *
    * @param Ticket $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Ticket $entity)
    {
        $form = $this->createForm(new TicketType(), $entity, array(
            'action' => $this->generateUrl('ticket_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Ticket entity.
     *
     * @Route("/{id}", name="ticket_update")
     * @Method("PUT")
     * @Template("GinsbergTransportationBundle:Ticket:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('GinsbergTransportationBundle:Ticket')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Ticket entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('ticket_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Ticket entity.
     *
     * @Route("/{id}", name="ticket_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('GinsbergTransportationBundle:Ticket')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Ticket entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('ticket'));
    }

    /**
     * Creates a form to delete a Ticket entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('ticket_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
