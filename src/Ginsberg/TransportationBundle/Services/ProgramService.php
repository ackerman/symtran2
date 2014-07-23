<?php

namespace Ginsberg\TransportationBundle\Services;

use Doctrine\ORM\EntityRepository;
use Monolog\Logger;
use Ginsberg\TransportationBundle\Entity\ProgramRepository;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class ProgramService
{
  private $programRepository;
  private $logger;
  
  public function __construct(ProgramRepository $programRepository, \Monolog\Logger $logger) {
    $this->programRepository = $programRepository;
    $this->logger = $logger;
  }
  
  
}

