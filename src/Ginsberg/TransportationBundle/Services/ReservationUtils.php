<?php

namespace Ginsberg\TransportationBundle\Services;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\RequestStack;
use \DateTime;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class ReservationUtils
{  
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
    $date = new DateTime($date);

		/*
		$fallback = Installation::get_fall_back();
		$springforward = Installation::get_spring_forward();
		//Yii::log('In assign_reservation_to_vehicle. Assignment failed.', 'info', 'system.debug');
		Yii::log('fallback: ' . $fallback , 'info', 'system.debug');
		Yii::log("springforward: " . $springforward , "info", "system.debug");
		if ($date <= $fallback && ($date + $interval) >= $fallback) {
			$date += $interval + 60 * 60;
			//Yii::log("***Start in between: "  . date('Y-m-d H:i:s', $start_datetime) , "info", "system.debug");
		} elseif ($date <= $springforward && ($date + $interval) >= $springforward) {
			$date += $interval - 60 * 60;
		} else {
			$date += $interval;
			//Yii::log("Start NOT in between: "  . date('Y-m-d H:i:s', $start_datetime) , "info", "system.debug");
		}
		*/
		return $date;
	}
}


