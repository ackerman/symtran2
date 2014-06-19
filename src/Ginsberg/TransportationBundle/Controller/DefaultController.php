<?php

namespace Ginsberg\TransportationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Default controller.
 *
 * @Route("/")
 */
class DefaultController extends Controller
{
    public function indexAction()
    {
      return $this->redirect($this->generateUrl('program_index'));
        //return $this->render('GinsbergTransportationBundle:Default:index.html.twig', array('name' => $name));
    }
}
