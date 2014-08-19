<?php

namespace Ginsberg\TransportationBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Monolog\Logger;

/**
 * @Annotation
 */
class DriverIsApproved extends Constraint
{
  public $message = '%string% is not approved to drive.';
  
  public function validatedBy() {
    return 'approvedToDrive';
  }
  
}