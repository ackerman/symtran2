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
    $this->logger->info('In InstallationService::getIsHoliday()');
    $params = array('date' => $date);
    $dql = 'SELECT COUNT(i) FROM GinsbergTransportationBundle:Installation i WHERE 
            :date BETWEEN i.thanksgivingStart AND i.thanksgivingEnd
            OR :date BETWEEN i.mlkStart AND i.mlkEnd
            OR :date BETWEEN i.springbreakStart AND i.springbreakEnd';
    
    $query = $this->em->createQuery($dql)->setParameters($params);

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
    $this->logger->info('In InstallationService::getIsSemesterBreak()');
    
		$params = array('date' => $date);
    $dql = 'SELECT COUNT(i) FROM GinsbergTransportationBundle:Installation i WHERE 
            :date < i.fallStart
            OR :date BETWEEN i.fallEnd AND i.winterStart
            OR :date > i.winterEnd';
    
    $query = $this->em->createQuery($dql)->setParameters($params);

    try {
      $result = $query->getSingleScalarResult();
      return ((bool) $result) ? TRUE : FALSE;
    } catch (\Doctrine\ORM\NoResultException $ex) {
      return null;
    }
  }
  
  // TODO: Need to force all reservations to be in the same semester
  /**
	 * Return whether "start" and "repeatsUntil" are in the same semester
   * 
   * @param datetime $date The date to check
   * 
   * @return bool|null Whether start and repeatsUntil are in the same semester
   * 
   * @throws \Doctrine\ORM\NoResultException
	 */
	public function getReservationsAreInOneSemester($start, $repeatsUntil) {
    $this->logger->info('In InstallationService::getIsSemesterBreak()');
    
		$params = array('start' => $date, repeatsUntil);
    $dql = 'SELECT COUNT(i) FROM GinsbergTransportationBundle:Installation i WHERE 
            :date < i.fallStart
            OR :date BETWEEN i.fallEnd AND i.winterStart
            OR :date > winterEnd';
    
    $query = $this->em->createQuery($dql)->setParameters($params);

    try {
      $result = $query->getSingleScalarResult();
      return ((bool) $result) ? TRUE : FALSE;
    } catch (\Doctrine\ORM\NoResultException $ex) {
      return null;
    }
  }
}

