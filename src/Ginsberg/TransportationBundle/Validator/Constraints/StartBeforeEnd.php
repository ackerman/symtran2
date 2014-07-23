<?php

namespace Ginsberg\TransportationBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Monolog\Logger;

/**
 * @Annotation
 */
class StartBeforeEnd extends Constraint
{
  public $message = 'The Start date and time must come before the End date and time';
  
  public function validatedBy() {
    return 'start_before_end';
  }
  
}