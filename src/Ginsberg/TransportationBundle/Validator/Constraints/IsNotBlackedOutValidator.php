<?php

namespace Ginsberg\TransportationBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Ginsberg\TransportationBundle\Entity\InstallationRepository; 
use Ginsberg\TransportationBundle\Services\InstallationService;

class IsNotBlackedOutValidator extends ConstraintValidator
{
  public function validate($value, Constraint $constraint) {
    $date = clone($value);
    $dateString = date('Y-m-d', $date->getTimestamp());
    $installationService = $this->get('installation_service');
    
    if ($installationService->getIsHoliday($value))
    {
      $this->context->addViolation(
        $constraint->message,
        array('%string%' => $dateString)
        );
    } elseif ($installationService->getIsSemesterBreak($value))
    {
      $date = clone($value);
      $dateString = date('Y-m-d', $date->getTimestamp());
      $this->context->addViolation(
        $constraint->message,
        array('%string%' => $dateString)
        );
    }
  }
  
}

