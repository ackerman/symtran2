<?php

namespace Ginsberg\TransportationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContextInterface;
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
class SecurityController extends Controller
{
 /**
  * Login page.
  */
 public function loginAction(Request $request)
 {
   $session = $request->getSession();
   
   // Get the login error if there is one
   if ($request->attributes->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
     $error = $request->attributes->get(SecurityContextInterface::AUTHENTICATION_ERROR);
   } elseif (null !== $session && $session->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
     $error = $session->get(SecurityContextInterface::AUTHENTICATION_ERROR);
     $session->remove(SecurityContextInterface::AUTHENTICATION_ERROR);
   } else {
     $error = '';
   }
   
   $lastUsername = (NULL === $session) ? '' : $session->get(SecurityContextInterface::LAST_USERNAME);
   
   return $this->render('GinsbergTransportationBundle:Security:login.html.twig', array(
     // last username entered by the user
     'last_username' => $lastUsername,
     'error' => $error,     
   ));
     //return $this->render('GinsbergTransportationBundle:Default:index.html.twig', array('name' => $name));
 }
    
}
