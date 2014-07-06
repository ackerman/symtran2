<?php

namespace Ginsberg\TransportationBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Ginsberg\TransportationBundle\Entity\Installation;
use Ginsberg\TransportationBundle\Form\InstallationType;

/**
 * Installation controller.
 *
 * @Route("/installation")
 */
class InstallationController extends Controller
{

    /**
     * Lists all Installation entities.
     *
     * @Route("/", name="installation")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('GinsbergTransportationBundle:Installation')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Installation entity.
     *
     * @Route("/", name="installation_create")
     * @Method("POST")
     * @Template("GinsbergTransportationBundle:Installation:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Installation();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('installation_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Installation entity.
     *
     * @param Installation $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Installation $entity)
    {
        $form = $this->createForm(new InstallationType(), $entity, array(
            'action' => $this->generateUrl('installation_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Installation entity.
     *
     * @Route("/new", name="installation_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Installation();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Installation entity.
     *
     * @Route("/{id}", name="installation_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('GinsbergTransportationBundle:Installation')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Installation entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Installation entity.
     *
     * @Route("/{id}/edit", name="installation_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('GinsbergTransportationBundle:Installation')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Installation entity.');
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
    * Creates a form to edit a Installation entity.
    *
    * @param Installation $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Installation $entity)
    {
        $form = $this->createForm(new InstallationType(), $entity, array(
            'action' => $this->generateUrl('installation_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Installation entity.
     *
     * @Route("/{id}", name="installation_update")
     * @Method("PUT")
     * @Template("GinsbergTransportationBundle:Installation:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('GinsbergTransportationBundle:Installation')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Installation entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('installation_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Installation entity.
     *
     * @Route("/{id}", name="installation_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('GinsbergTransportationBundle:Installation')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Installation entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('installation'));
    }

    /**
     * Creates a form to delete a Installation entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('installation_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
