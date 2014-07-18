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
  
  /**
  * Get Program name based on official name of the program's MCommunity eligibility group.
  */
  public function get_program_name_by_ldap_group($ldap_group) 
  {
    $program = $this->programRepository->findByEligibilityGroup($ldap_group);
    $this->logger->info('in get_program_name_by_ldap_group. $program = ' . var_dump($program));
    if ($program) {
      return true;
    } else {
      return false;
    }
  }
  
  /**
  * Get Program Id based on official name of the program's MCommunity eligibility group.
  */
  public static function get_program_id_by_ldap_group($ldap_group) 
  {
    $program = $this->programRepository->findBy($ldap_group);
    if ($program) {
      return $program->getId();
    } else {
      return false;
    }
  }
}

