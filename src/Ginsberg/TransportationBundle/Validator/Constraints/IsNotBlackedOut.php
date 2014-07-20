<?php

namespace Ginsberg\TransportationBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Monolog\Logger;

/**
 * @Annotation
 */
class IsNotBlackedOut extends Constraint
{
  public $message = 'The Ginsberg Center is closed on %string%. Please choose another date.';
  
  public function validatedBy() {
    return 'blackout';
  }
  
}