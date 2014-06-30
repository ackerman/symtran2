<?php

namespace Ginsberg\TransportationBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Ginsberg\TransportationBundle\Entity\Destination;
use Ginsberg\TransportationBundle\Form\DestinationType;

/**
 * Destination controller.
 *
 * @Route("/destination")
 */
class DestinationController extends Controller
{

    /**
     * Lists all Destination entities.
     *
     * @Route("/", name="destination")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        //$entities = $em->getRepository('GinsbergTransportationBundle:Destination')->findAll();
        $entities = $this->getDoctrine()->getRepository('GinsbergTransportationBundle:Destination')
            ->findByProgramSortedByProgram(TRUE);
        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Destination entity.
     *
     * @Route("/", name="destination_create")
     * @Method("POST")
     * @Template("GinsbergTransportationBundle:Destination:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Destination();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('destination_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a Destination entity.
    *
    * @param Destination $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Destination $entity)
    {
        $form = $this->createForm(new DestinationType(), $entity, array(
            'action' => $this->generateUrl('destination_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Destination entity.
     *
     * @Route("/new", name="destination_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Destination();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Destination entity.
     *
     * @Route("/{id}", name="destination_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('GinsbergTransportationBundle:Destination')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Destination entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Destination entity.
     *
     * @Route("/{id}/edit", name="destination_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('GinsbergTransportationBundle:Destination')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Destination entity.');
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
    * Creates a form to edit a Destination entity.
    *
    * @param Destination $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Destination $entity)
    {
        $form = $this->createForm(new DestinationType(), $entity, array(
            'action' => $this->generateUrl('destination_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Destination entity.
     *
     * @Route("/{id}", name="destination_update")
     * @Method("PUT")
     * @Template("GinsbergTransportationBundle:Destination:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('GinsbergTransportationBundle:Destination')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Destination entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('destination_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Destination entity.
     *
     * @Route("/{id}", name="destination_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('GinsbergTransportationBundle:Destination')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Destination entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('destination'));
    }

    /**
     * Creates a form to delete a Destination entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('destination_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
