<?php

namespace Ginsberg\TransportationBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Ginsberg\TransportationBundle\Entity\InstallationRepository; 
use Ginsberg\TransportationBundle\Services\InstallationService;
use Monolog\Logger;

class IsNotBlackedOutValidator extends ConstraintValidator
{
  public $installationService;
  
  public function __construct($installationService, $logger) {
    $this->installationService = $installationService;
    $this->logger = $logger;
  }
  
  public function validate($value, Constraint $constraint) 
  {
    if ($this->installationService->getIsHoliday($value)) {
      $this->logger->info("In IsNotBlackedOutValidator::validate(). getIsHoliday returned true.");
      $date = clone($value);
      $dateString = date('D, M d', $date->getTimestamp());
      $this->context->addViolation(
        $constraint->message,
        array('%string%' => $dateString)
        );
    } elseif ($this->installationService->getIsSemesterBreak($value)) {
      $this->logger->info("In IsNotBlackedOutValidator::validate(). getIsSemesterBreak returned true.");
      $date = clone($value);
      $dateString = date('D, M d', $date->getTimestamp());
      $this->context->addViolation(
        $constraint->message,
        array('%string%' => $dateString)
        );
    }
    //$logger->info("Done with IsNotBlackedOutValidator::validate().");
      
  }
  
}

