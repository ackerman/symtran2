<?php

namespace Ginsberg\TransportationBundle\Entity;

use Doctrine\ORM\EntityRepository;

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
	 * Return whether or not a date falls on a Ginsberg holiday
	 */
	public static function is_semester_break($date) {
		$check_date = Installation::model()->count(
      ':date < fall_start OR
			:date BETWEEN fall_end AND winter_start OR
			:date > winter_end',
      array(
        ':date' => $date,
      )
    );
		if ( (bool) $check_date ):
      return true;
    endif;
    return false;
	}
}
