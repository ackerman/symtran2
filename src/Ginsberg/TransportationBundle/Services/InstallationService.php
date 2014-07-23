<?php

namespace Ginsberg\TransportationBundle\Services;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Ginsberg\TransportationBundle\Entity\InstallationRepository;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class InstallationService
{
  private $installationRepository;
  private $em;
  private $logger;
  
  public function __construct($entityManager, InstallationRepository $installationRepository, \Monolog\Logger $logger) {
    $this->installationRepository = $installationRepository;
    $this->logger = $logger;
    $this->em = $entityManager;
  }
  
  
}

