<?php

namespace Ginsberg\TransportationBundle\Services;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\RequestStack;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class ReservationUtils
{
  protected $requestStack;
  
  public function __construct(RequestStack $requestStack) {
    $this->requestStack = $requestStack;
  }
  
}


