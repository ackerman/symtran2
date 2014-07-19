<?php

namespace Ginsberg\TransportationBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class IsNotBlackoutDate extends Constraint
{
  public $message = 'The Ginsberg Center is closed on "%string". Please choose another date.';
  
  public function validatedBy()
  {
    return 'blackout';
  }
}