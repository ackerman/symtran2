<?php

namespace Ginsberg\TransportationBundle\Services;

use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Ginsberg\TransportationBundle\Entity\VehicleRepository;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class ReservationUtils
{  
  protected $requestStack;
  protected $em;
  protected $vehicleRepository;
  protected $logger;
  
  public function __construct(RequestStack $requestStack, \Doctrine\ORM\EntityManager $entityManager, VehicleRepository $vehicleRepository, \Monolog\Logger $logger) {
    $this->requestStack = $requestStack;
    $this->vehicleRepository = $vehicleRepository;
    $this->logger = $logger;
    $this->em = $entityManager;
  }
  public function getRepeatInterval($date) {
		// DO NOT USE PHP datetime CALCULATIONS. THEY ADJUST RESERVATION TIMES
    // FOR DAYLIGHT SAVINGS TIME, WHICH IS _NOT_ WHAT WE WANT.
    // $date is a DateTime. Split it into the time portion and the day portion
		// Add one week to the day portion, then concatenate the new day with the time.
		// Finally, convert to a timestamp and return.
		$interval = (7 * 24 * 60 * 60 + 3 * 60 * 60); // 7 days + 3 hour fudge factor for daylight savings time (1 week) * 24 hours * 60 minutes * 60 seconds
    $time = date('H:i', $date->getTimestamp());
		$day = date('Y-m-d', $date->getTimestamp());
		$day = strtotime($day) + $interval;
		$date = date('Y-m-d', $day) . ' ' . $time;
    $date = new \DateTime($date);

		return $date;
	}
  
  // The following functions support the calendar view widget (_view_calendar.php)
	public function get_reservation_left_position($data)
  {
    $cars = $this->vehicleRepository->findAllActiveVehiclesSortedByProgram();
    $cars_array = array();
    $car_position = 15;
    $left = '';
    foreach ($cars as $car) {
      $cars_array[$car->getName()] = $car->getId();
    }

    foreach ($cars_array as $car) {
      $left=$car_position;
      if ($car==$data->getVehicle()->getId())
      {
        break;
      }
      $car_position += 137;
    }
    return $left;
  }

  public function get_reservation_top_position($data)
  {
    $start = clone($data->getStart());
    $start_timestamp = strtotime($start->format('Y-m-d H:i:s'));
    $start_time = date("G",$start_timestamp);
    $start_time_minutes = date("i", $start_timestamp)/60;
    $start_time += $start_time_minutes;

    // Adjust for the day starting at 7am
    // Set this reservation's top position to the start_time times 38 (pixels) minus
    // one of those 38 (pixels) for every hour that is not shown (the first 7 hours).
    $top = $start_time * 38 - 7 * 38;
    return $top;
  }

  public function get_reservation_height($data) {
    $height = '';
    $start = clone($data->getStart());
    $start_timestamp = strtotime($start->format('Y-m-d H:i:s'));
    $top_diff_array = $this->date_diff($data->getStart()->format('Y-m-d H:i:s'), $data->getEnd()->format('Y-m-d H:i:s'));
    $height=$top_diff_array['minutes_total']/60*38;

    return $height;
  }

  public function get_adjusted_height_and_top($data)
  {
    $end = $data->getEnd()->format('Y-d-m H:i:s');
    $date = '';
    if ($this->requestStack->getCurrentRequest()->get('dateToShow')) {
      $date = $this->requestStack->getCurrentRequest()->get('dateToShow')->format('Y-m-d');
    } else {
      $date = new \DateTime();
      $date = $date->format('Y-m-d');
    }
		$height_top_array = '';
    $start = clone($data->getStart());
    $start_timestamp = strtotime($start->format('Y-m-d H:i:s'));
    // Calculate whether starttime is between midnight and 7am
    $start_between_midnight_and_seven;
    if ($start_timestamp >= strtotime($date) && $start_timestamp < mktime(7, 0, 0, date('m', $start_timestamp), date('d', $start_timestamp), date('Y', $start_timestamp))) {
      $start_between_midnight_and_seven = true;
    } else {
      $start_between_midnight_and_seven = false;
    }

    // If reservation began on an earlier day or on this day but before 7am, set top to 7am and adjust height
    if ($start_timestamp < strtotime($date) || $start_between_midnight_and_seven) {
      $height_top_array['top'] = 0;
      $height_array = $this->date_diff($date, $end);
      $height_top_array['height'] = $height_array['minutes_total']/60*38 - 7 * 38;
    }
    return $height_top_array;
  }
  
  public function date_diff($d1, $d2){
    $d1 = (is_string($d1) ? strtotime($d1) : $d1);
    $d2 = (is_string($d2) ? strtotime($d2) : $d2);

    $diff_secs = abs($d1 - $d2);
    $base_year = min(date("Y", $d1), date("Y", $d2));

    $diff = mktime(0, 0, $diff_secs, 1, 1, $base_year);
    return array(
      "years" => date("Y", $diff) - $base_year,
      "months_total" => (date("Y", $diff) - $base_year) * 12 + date("n", $diff) - 1,
      "months" => date("n", $diff) - 1,
      "days_total" => floor($diff_secs / (3600 * 24)),
      "days" => date("j", $diff) - 1,
      "hours_total" => floor($diff_secs / 3600),
      "hours" => date("G", $diff),
      "minutes_total" => floor($diff_secs / 60),
      "minutes" => (int) date("i", $diff),
      "seconds_total" => $diff_secs,
      "seconds" => (int) date("s", $diff)
    );
	}
}


