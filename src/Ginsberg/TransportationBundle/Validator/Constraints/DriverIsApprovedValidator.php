<?php

namespace Ginsberg\TransportationBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Ginsberg\TransportationBundle\Entity\InstallationRepository;
use Doctrine\ORM\EntityManager;
use Ginsberg\TransportationBundle\Services\InstallationService;
use Monolog\Logger;

class DriverIsApprovedValidator extends ConstraintValidator
{
  public $personRepository;
  
  public function __construct($personRepository, $logger) {
    $this->personRepository = $personRepository;
    $this->logger = $logger;
  }
  
  public function validate($value, Constraint $constraint) 
  {
    if (!$this->personRepository->isApproved($value)) {
      $this->logger->info("In DriverIsApproved::validate(). isApproved returned false.");
      
      $this->context->addViolation(
        $constraint->message,
        array('%string%' => $value)
        );
    } 
  }
  
}

