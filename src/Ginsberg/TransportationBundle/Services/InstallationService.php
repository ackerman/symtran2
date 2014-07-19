<?php

namespace Ginsberg\TransportationBundle\Services;

use Doctrine\ORM\EntityRepository;
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
  private $logger;
  
  public function __construct(InstallationRepository $installationRepository, \Monolog\Logger $logger) {
    $this->installationRepository = $installationRepository;
    $this->logger = $logger;
  }
  
  /**
	 * Return whether or not a date falls on a Ginsberg holiday
   * 
   * @param datetime $date The date to check
   * 
   * @return bool|null Whether or not the date is a holiday
   * 
   * @throws \Doctrine\ORM\NoResultException
	 */
	public function getIsHoliday($date) {
    $params = array('date' => $date);
    $dql = 'SELECT COUNT(i) FROM GinsbergTransportationBundle:Installation i WHERE 
            :date BETWEEN i.thanksgivingStart AND i.thanksgivingEnd
            OR :date BETWEEN i.mlkStart AND i.mlkEnd
            OR :date BETWEEN i.springbreakStart AND i.springbreakEnd';
    
    $query = $this->getEntityManager()->createQuery($dql)->setParameters($params);

    try {
      $result = $query->getSingleScalarResult();
      return ((bool) $result) ? TRUE : FALSE;
    } catch (\Doctrine\ORM\NoResultException $ex) {
      return null;
    }
	}

	/**
	 * Return whether or not a date falls during a semester break
   * 
   * @param datetime $date The date to check
   * 
   * @return bool|null Whether or not the date falls during a semester break
   * 
   * @throws \Doctrine\ORM\NoResultException
	 */
	public function getIsSemesterBreak($date) {
		$params = array('date' => $date);
    $dql = 'SELECT COUNT(i) FROM GinsbergTransportationBundle:Installation i WHERE 
            :date < i.fallStart
            OR :date BETWEEN i.fallEnd AND i.winterStart
            OR :date > winterEnd';
    
    $query = $this->getEntityManager()->createQuery($dql)->setParameters($params);

    try {
      $result = $query->getSingleScalarResult();
      return ((bool) $result) ? TRUE : FALSE;
    } catch (\Doctrine\ORM\NoResultException $ex) {
      return null;
    }
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

