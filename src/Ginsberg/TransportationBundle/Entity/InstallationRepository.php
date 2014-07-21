<?php

namespace Ginsberg\TransportationBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;

/**
 * InstallationRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class InstallationRepository extends EntityRepository
{
  /**
   * Validation rule for blackout start and end dates.
   * Ensures that dates are properly interpreted.
   * Dates in 1969 are not valid.
   */
	public function date_is_valid($attribute, $params)
	{
	  $value = date('Y-m-d H:i:s', strtotime($this->$attribute));
	  if( substr($value, 0, 4) === '1969'):
	    $this->addError($attribute,yii::t($attribute,'Sorry, we didn\'t understand the date you entered.'));
	    $this->$attribute = '';
	  endif;
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
    //$this->logger->info('In InstallationService::getIsHoliday()');
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
    //$this->logger->info('In InstallationService::getIsSemesterBreak()');
    
		$params = array('date' => $date);
    $dql = 'SELECT COUNT(i) FROM GinsbergTransportationBundle:Installation i WHERE 
            :date < i.fallStart
            OR :date BETWEEN i.fallEnd AND i.winterStart
            OR :date > i.winterEnd';
    
    $query = $this->getEntityManager()->createQuery($dql)->setParameters($params);

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
    //$this->logger->info('In InstallationService::getIsSemesterBreak()');
    
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
