<?php

namespace Ginsberg\TransportationBundle\Services;

use Doctrine\ORM\EntityRepository;
use Monolog\Logger;
use Ginsberg\TransportationBundle\Entity\PersonRepository;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class PersonService
{
  private $personRepository;
  private $logger;
  
  public function __construct(PersonRepository $personRepository, \Monolog\Logger $logger) {
    $this->personRepository = $personRepository;
    $this->logger = $logger;
  }
  
  /**
   * Return the full name of the Person by provided uniqname.
   * 
   * Since the Person __toString() method returns the Person's full name, the  
   * same effect can probably be accomplished with:
   * (string) $person->findByUniqname($uniqname)
   * 
   * @param type $uniqname
   * @return string|boolean 
   */
  public function getFullNameByUniqname($uniqname)
	{
    $person = $this->personRepository->findByUniqname($uniqname);
    if ($person)
    {
      return $person->getFirstName() . ' ' . $person->getLastName();
    } else 
    {
      return False;
    }
	}
  
  public function getStatusByUniqname($uniqname) 
  {
    $person = $this->personRepository->findByUniqname($uniqname);
    $this->logger->info('person = ' . var_dump($person));
    return $person->getStatus();
  }
  
  public function convert_pts_status_to_gc_status($pts_status) {
		if ($pts_status == "Submitted" || $pts_status == "Waiting for Documentation") {
			$gc_status = 'pending';
		} elseif ($pts_status == "Approved") {
			$gc_status = 'approved';
		} elseif ($pts_status == 'Not Approved') {
			$gc_status = 'rejected';
		}
		return $gc_status;
	}

}

