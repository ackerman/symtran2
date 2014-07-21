<?php

namespace Ginsberg\TransportationBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * ProgramRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProgramRepository extends EntityRepository
{
  /**
  * Get Program name based on official name of the program's MCommunity eligibility group.
  */
  public function getProgramNameByLdapGroup($ldapGroup = FALSE) 
  {
    if ($ldapGroup) {
      $program = $this->findByEligibilityGroup($ldapGroup);
      //$this->logger->info('in get_program_name_by_ldap_group. $programId = ' . $programId);
      return $program->getName();
    } else {
      return false;
    }
  }
  
  /**
  * Get Program Id based on official name of the program's MCommunity eligibility group.
  */
  public static function get_program_id_by_ldap_group($ldapGroup) 
  {
    $program = $this->programRepository->findByEligibilityGroup($ldapGroup);
    if ($program) {
      return $program->getId();
    } else {
      return false;
    }
  }
}
