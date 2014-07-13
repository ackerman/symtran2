<?php

namespace Ginsberg\TransportationBundle\Security\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Ginsberg\TransportationBundle\Entity\Person;
use Doctrine\ORM\EntityRepository;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class UserProvider implements UserProviderInterface
{
  private $personRepository;
  
   protected static $_host = 'ldap.itd.umich.edu';
    // The umbrella group that lists the subgroups of eligible drivers
    protected static $_eligible_group = 'ginsberg transpo eligible';
    protected static $_admin_group = 'ginsberg transportation admins';
    protected static $_superuser_group = 'ginsberg transportation superusers';
    protected static $_pts_group = 'ginsberg pts staff';
    public static $_pts_group_email = 'ginsberg-pts-staff@umich.edu';

    public function loadUserByUsername($uniqname)
    {
      $password = "admin";
      $salt = "";
      $roles = array();
    
      if (self::is_authenticated()) {
        if (self::is_superuser()) {
          $roles[] = 'ROLE_SUPER_ADMIN';
        } elseif (self::is_admin()) {
          $roles[] = 'ROLE_ADMIN';
        } elseif (self::is_eligible()) {
          $roles[] = 'ROLE_USER';
        }
        
        return new User($uniqname, $password, $salt, $roles);
      }
      
      throw new UsernameNotFoundException(
      sprintf('Username "%s" does not exist.', $uniqname));
    }
    
    public function refreshUser(UserInterface $user) {
      if (!$user instanceof User) {
        throw new UnsupportedUserException(
          sprintf('Instances of "%s" are not supported.', get_class($user))
        );
      }
      
      return $this->loadUserByUsername($user->getUsername());
    }
    
    public function supportsClass($class) {
      return $class === 'Ginsberg\TransportationBundle\Security\User\User.php';
    }

    /**
     * Gets uniqname based on value of $_SERVER['REMOTE_USER'] or supply hard-coded value for testing
     *
     * @return string User's uniqname or false if not found
     */
    public static function get_uniqname()
    {
      // If we are in a cosign environment, return the user uniqname from
      // REMOTE_USER
      if (isset( $_SERVER['REMOTE_USER'] ) && !empty( $_SERVER['REMOTE_USER'] )) {
        return $_SERVER['REMOTE_USER'];
      }

      // for local testing (aka PHP is running under phpunit)
      if($_SERVER[ 'PHP_SELF' ] === '/usr/local/bin/phpunit') {
        return 'ericaack';
      }

      // for local debug:
      if(!isset( $_SERVER['REMOTE_USER'] ) &&
          $_SERVER[ 'SERVER_NAME' ] === 'localhost') {
        return 'ericaack';
      }

      //Yii::log('User not found', 'info','system.debug');
      return false;
    }


    /**
     * Check whether user is logged in through Cosign.
     *
     * @return boolean Whether or not the user is authenticated
     */
    public static function is_authenticated()
    {
      if (self::get_uniqname() != False) {
        return True;
      }
      return False;
    }

    /**
     * Return user's email
     *
     * @return string The user's email
     */
    public static function get_email()
    {
      return self::get_uniqname() . '@umich.edu';
    }


    /**
     * Find the current user's LDAP entry.
     *
     * Used for pulling the first and last name, any other personal info during
     * registration or when checking user's access
     *
     * @return mixed The user's ldap entry or false on failure
     */
    public static function get_ldap_entry()
    {
      $uniqname = self::get_uniqname();
      $ldap = ldap_connect(self::$_host);
      if ($ldap === False) {
        return False;
      }

      // Search for the user
      $dn = "ou=People,dc=umich,dc=edu";

      $results = ldap_search($ldap, $dn, 'uid=' . $uniqname);
      $people = ldap_get_entries($ldap, $results);

      return $people[0];
    }


    /**
     * Checks our database and LDAP to get the user's full name
     *
     * @return string The full name of the user
     */
    public static function get_full_name()
    {
      // First check if the user is in the database
      $name_from_database = Person::get_full_name_by_uniqname(self::get_uniqname());
      if ($name_from_database) {
        return $name_from_database;
      }

      // If not, retrieve the full name with LDAP.
      $ldap_entry = self::get_ldap_entry();

      // Check if the user has shared their full name; if so, return it.
      if( array_key_exists('displayname', $ldap_entry)) {
        return $ldap_entry['displayname'][0];
      }
      return "";
    }


    /**
     * Checks our database and LDAP to get the user's first name
     *
     * @return string The first name of the user
     */
    public static function get_first_name()
    {
      // First check if the user is in the database
      $person = Person::find_by_uniqname(self::get_uniqname());
      if ($person){
        return $person->first_name;
      }

      // Then guess the first name from the LDAP entry
      $name_array = explode(" ", self::get_full_name());
      return $name_array[0];
    }


    /**
     * Checks our database and LDAP to get the user's last name
     *
     * @return string The user's last name
     */
    public static function get_last_name()
    {
      // First check if the user is in the database
      $person = Person::find_by_uniqname(self::get_uniqname());
      if ($person) {
        return $person->last_name;
      }

      // Not in DB, so guess the last name from the LDAP entry. Try surname first.
      $person = self::get_ldap_entry();
      if (array_key_exists('sn', $person)) {
        return $person['sn'][0];
      } elseif (array_key_exists('displayname', $person)) {
        $name_array = explode(" ", $person['displayname'][0]);
        return array_pop($name_array);
      } else {
        return "";
      }
    }

    /**
     * Checks whether or not user is approved in Ginsberg transpo database
     *
     * @return boolean Whether user is approved
     */
    public static function is_approved()
    {
      $uniqname = self::get_uniqname();
      $personRepository = $this->get('ginsberg_person');
      $status = $personRepository->findStatusByUniqname($uniqname);
        
      return($status == 'approved') ? TRUE : FALSE;
        
    }

    /**
     * Check whether user is in a given group or one of the group's optional subgroups.
     *
     * If $is_umbrella_group is set to TRUE (that is, if this group consists of sub-groups),
     * returns the name of the group the user is in (if any).
     * Otherwise simply returns TRUE or FALSE
     *
     * @param string $group_name
     * @param boolean $is_umbrella_group
     *
     * @return mixed The name of the group the user is in or else true or false
     */
    public static function is_in_which_LDAP_group($group_name, $is_umbrella_group = false)
    {
      //$umbrella_group = "ginsberg transpo eligible";
      $uniqname = self::get_uniqname();

      // Generate the DN strings.
      $basednstring = 'dc=umich, dc=edu';
      $groupdnstring = "ou=User Groups,ou=Groups,dc=umich,dc=edu";
      // Connect to LDAP.
      $ldap = ldap_connect(self::$_host);
      if ($ldap === false) {
        Yii::log("ldap_connect failed", "info", "system.debug");
        return false;
      }

      $userdn = self::_get_userdn(self::get_ldap_entry());
      //Yii::log('userdn = ' . $userdn, 'info','system.debug');
      if ($userdn == NULL) {
        Yii::log("No userdn retrieved for $uniqname", "info", "system.debug");
        return false;
      }

      if (!$is_umbrella_group) {
        return self::_is_user_in_group($ldap, $userdn, $group_name);
      }

      // Search for the umbrella group for all subgroups, returning array of subgroups
      $filter = 'cn=' . $group_name;
      //$group = self::_search_ldap($ldap, $dn, $filter);
      $subgroups = self::_get_ldap_subgroups($ldap, $groupdnstring, $filter);


      // Go through each of the subgroups and see if our current user is in it
      // Using strtolower() because sometimes the directory returns ou=People
      // and sometimes  ou=people.
      foreach ($subgroups as $subgroup) {
        //Yii::log('group being search is ' . $subgroup, 'info','system.debug');
        if (self::_is_user_in_group($ldap, $userdn, $subgroup)) {
          //Yii::log($uniqname . " IS in group " . $subgroup, "info", "system.debug");
          return $subgroup;
        }
      }

      //Yii::log($uniqname . " is not in group " . $group_name, "info", "system.debug");

      return false;
    }

    /**
     * Retrieves array of sub-groups from an umbrella group. Used by is_in_which_ldap_group().
     *
     * @param resource $ldap
     * @param string $dn
     * @param string $filter
     */
    protected static function _get_ldap_subgroups($ldap, $dn, $filter) {
      $results = ldap_search($ldap, $dn, $filter);
      $group = ldap_get_entries($ldap, $results);
      $subgroups = array();
      foreach($group[0]['member'] as $value) {
        if(strpos($value, 'cn=') !== false) {
          $group_name = substr($value, 3, strpos($value, ',') - 3);
          $subgroups[] = $group_name;
        }
      }
      return $subgroups;
    }

    /**
    * Checks group membership of the user, searching only in specified group (not recursively).
    *
    * @param resource $ldap
    * @param string $userdn
    * @param string $group
    */
    protected static function _is_user_in_group($ldap, $userdn, $group) {
        $filter = '(&(member=' . $userdn . ')(cn=' . $group . '))';
        $result = ldap_search($ldap, 'ou=User Groups,ou=Groups,dc=umich,dc=edu', $filter);
        if ($result === FALSE) {
          //Yii::log("$userdn not in $group", "info", "system.debug");
          return FALSE;
        }
        $entries = ldap_get_entries($ldap, $result);
        return ($entries['count'] > 0);
    }


    /**
     * Get User's LDAP dn from attributes retrieved by get_ldap_entry().
     *
     * @param array $ldap_attrs
     *
     * @return mixed User's 'dn' or NULL
     */
    protected static function _get_userdn($ldap_attrs) {
      if (!empty($ldap_attrs)) {
        return $ldap_attrs['dn'];
      } else {
        return NULL;
      }
    }


    /**
     * Checks if the current user is in the LDAP group of all eligible drivers
     *
     * @return mixed Name of eligibility group or FALSE
     */
    public static function is_eligible()
    {
      // Look through all subgroups in umbrella group $_eligible_group
      $umbrella = true;
      //Yii::log("User is in ". self::is_in_which_LDAP_group(self::$_eligible_group, $umbrella), "info", "system.debug");
      return self::is_in_which_LDAP_group(self::$_eligible_group, $umbrella);
    }

    /**
     * Checks if the current user is in the LDAP group that sets administrators
     *
     * @return mixed Name of admin group or FALSE
     */
    public static function is_admin()
    {
      // The group that lists the site administrators:
      // The user we are looking for
      if (self::is_in_which_LDAP_group(self::$_admin_group)) {
        //Yii::log('true, user is in admin group', 'info','system.debug');
      } else {
        //Yii::log('false, user is NOT in admin group', 'info','system.debug');
      }
      return self::is_in_which_LDAP_group(self::$_admin_group);
    }

    /**
     * Checks if the current user is in the LDAP group that sets superusers
     *
     * Only superusers can delete Person records
     *
     * @return mixed Name of superuser group or FALSE
     */
    public static function is_superuser()
    {
      // The group that lists the site superusers:
      return self::is_in_which_LDAP_group(self::$_superuser_group);
    }

    /**
     * Check whether logged-in user is a member of the PTS staff
     *
     * @todo With PTS MVR integration, we shouldn't need to have PTS staff log in anymore
     *
     * @return mixed Name of the PTS transportation admin group, or FALSE
     */
    public static function is_transportation_admin()
    {
      // The group that lists the site administrators:
      return self::is_in_which_LDAP_group(self::$_pts_group);

    }

    /**
     * get data about the user from PTS.
     *
     * @param string $uniqname
     *
     * @result json	The JSON object representing the user's info in the PTS MVR system
     */
    public static function get_pts_info($uniqname) {
      $ch = curl_init("https://pts.umich.edu/internal/mvr/api/gins_api.php?uniqname=" . $uniqname);
      $password = 'TGC&PTSW2g';
      $username = 'gins-trans';
      $is_returntransfer = curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      $is_peer_verified = curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
      $is_content_type_set = curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/html",
                                                                        "X_PASSWORD: TGC&PTSW2g",
                                                                        "X_USERNAME: gins-trans"));
      $result = curl_exec($ch);
      if ($result === FALSE) {
        die("Curl failed: " . curl_error($ch));
      }

      //echo "Result is " . $result . "<br />\n";
      $jsonobj = json_decode($result);

      curl_close($ch);
      return $jsonobj;

    }

    /**
     * Get the PTS Status by the User's uniqname
     *
     * @param string $uniqname
     *
     * @return string User's status in the PTS MVR system
     */
    public static function get_pts_status_by_uniqname($uniqname) {
      $user = self::get_pts_info($uniqname);

      return $user->mvr_status;
    }

  }
