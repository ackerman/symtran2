<?php

namespace Ginsberg\TransportationBundle\Security\User;

/**
 * This file provides methods for managing user identity info for the logged-in user, 
 * including Cosign, LDAP, and PTS MVR data.
 *
 * @author Matt Hampel <hampelm@umich.edu>
 * @author Erica Ackerman <ericaack@umich.edu>
 */

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
/**
 * Manages user identity info for the logged-in user, including Cosign, LDAP, and PTS MVR data.
 *
 * User is a "component", a class that provides a lot of identity checking methods.
 * User is not an item in the database.
 * Do not confuse the User component with a Person, someone who has already been
 * added to the Ginsberg system. A Person is an item in the Ginsberg transpo database
 * and has a model, controller, and views.
 */
class User implements UserInterface, EquatableInterface
{
  private $uniqname;
  private $password;
  private $salt;
  private $roles;
  
 
  public function __construct($uniqname, $password, $salt, array $roles) {
    $this->uniqname = $uniqname;
    $this->password = $password;
    $this->salt = $salt;
    $this->roles = $roles;
  }

  public function getRoles()
  {
      return $this->roles;
  }

  public function getPassword()
  {
      return $this->password;
  }

  public function getSalt()
  {
      return $this->salt;
  }

  public function getUsername()
  {
      return $this->uniqname;
  }

  public function eraseCredentials()
  {
  }

  public function isEqualTo(UserInterface $user)
  {
      if (!$user instanceof User) {
          return false;
      }

      if ($this->password !== $user->getPassword()) {
          return false;
      }

      if ($this->getSalt() !== $user->getSalt()) {
          return false;
      }

      if ($this->uniqname !== $user->getUsername()) {
          return false;
      }

      return true;
  }

}
