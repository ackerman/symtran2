<?php
  namespace Ginsberg\TransportationBundle\Form\DataTransformer;

  use Symfony\Component\Form\DataTransformerInterface;
  use Symfony\Component\Form\Exception\TransformationFailedException;
  use Doctrine\Common\Persistence\ObjectManager;
  use Ginsberg\TransportationBundle\Entity\Person;

  class PersonToStringTransformer implements DataTransformerInterface
  {
      /**
       * @var ObjectManager
       */
      private $om;

      /**
       * @param ObjectManager $om
       */
      public function __construct(ObjectManager $om)
      {
          $this->om = $om;
      }

      /**
       * Transforms an object (person) to a string (number).
       *
       * @param  Person|null $person
       * @return string The Person's uniqname
       */
      public function transform($person)
      {
          if (null === $person) {
              return "";
          }

          return $person->getUniqname();
      }

      /**
       * Transforms a string (uniqname) to an object (person).
       *
       * @param  string $uniqname
       *
       * @return Issue|null
       *
       * @throws TransformationFailedException if object (person) is not found.
       */
      public function reverseTransform($uniqname)
      {
          if (!$uniqname) {
              return null;
          }

          $person = $this->om
              ->getRepository('GinsbergTransportationBundle:Person')
              ->findOneBy(array('uniqname' => $uniqname))
          ;

          if (null === $person) {
              throw new TransformationFailedException(sprintf(
                  'A person with uniqname "%s" does not exist!',
                  $uniqname
              ));
          }

          return $person;
      }
  }

