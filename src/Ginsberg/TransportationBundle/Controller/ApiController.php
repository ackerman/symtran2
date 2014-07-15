<?php

namespace Ginsberg\TransportationBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Ginsberg\TransportationBundle\Entity\Person;

class ApiController extends Controller 
{

  // Members
  /**
   * Key which has to be in HTTP USERNAME and PASSWORD headers
   */
  Const APPLICATION_ID = 'ASCCPE';

  /**
   * Default response format
   * either 'json' or 'xml'
   */
  private $format = 'json';

  // Actions
  public function actionCreate() {
    $logger = $this->get('logger');
    $this->_checkAuth();
    $model = new Person();
    $uniqname = '';
    $status = '';
    if (isset($_GET['uniqname'])) {
      $uniqname = $_GET['uniqname'];
    } else {
      $logger->info('uniqname not set ');
    }

    $model->uniqname = $uniqname;
    $model->status = $_GET['status'];

    $model->first_name = ldap_get_first($uniqname);
    $model->last_name = ldap_get_last($uniqname);
    $model->program = ldap_get_program($uniqname);


    if ($model->save()) {
      $json = CJSON::encode($model);
      $this->_sendResponse(200, CJSON::encode($model));
    } else {
      // Errors occurred
      $msg = CJSON::encode($model->errors);
      /*
      $msg = "<h1>Error</h1>";
      $msg .= "<p>Couldn't save model,</p>";
      $msg .= "<ul>";
      foreach($model->errors as $attribute=>$attr_errors) {
        if($attr_errors) {
          $msg .= "<li>Attribute: $attribute</li>";
          $msg .= "<ul>";
          foreach ($attr_errors as $attr_error) {
            $msg .= "<li>$attr_error</li>";
          }
          $msg .= "</ul>";
        }
      }
      $msg .= "</ul>";
      */
      $this->_sendResponse(500, $msg);
    }
  }

  public function actionUpdate() {

  }

  private function _sendResponse($status = 200, $body = '', $content_type = 'text/html') {
    // set the status
    $status_header = 'HTTP/1.1 ' . $status . ' ' . $this->_getStatusCodeMessage($status);
    header($status_header);
    // and the content type
    header('Content=type: ' . $content_type);
    $signature = "Standin";

    // if page has body
    if ($body != '') {
      $logger->info('body = ' . $body);
      // send the body
      echo $body;
    } else {
      // create some body message
      $message = '';
      switch($status)
        {
          case 401:
            $message = 'You must be authorized to view this page.';
            break;
          case 404:
            $message = 'The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found.';
            break;
          case 500:
            $message = 'The server encountered an error processing your request.';
            break;
          case 501:
            $message = 'The requested method is not implemented.';
            break;
        }
        // this should be templated in a real-world solution
        $body = '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <title>' . $status . ' ' . $this->_getStatusCodeMessage($status) . '</title>
</head>
<body>
    <h1>' . $this->_getStatusCodeMessage($status) . '</h1>
    <p>' . $message . '</p>
    <hr />
    <address>' . $signature . '</address>
</body>
</html>';

        echo $body;

    }
    Yii::app()->end();
  }

  private function _getStatusCodeMessage($status) {
    // these could be stored in a .ini file and loaded
    // via parse_ini_file()... however, this will suffice
    // for an example
    $codes = Array(
        200 => 'OK',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
    );
    return (isset($codes[$status])) ? $codes[$status] : '';
  }

  private function _checkAuth() {
    // Check if the USERNAME and PASSWORD HTTP headers have been set
    if (isset($_SERVER['HTTP_X_USERNAME']) && $_SERVER['HTTP_X_USERNAME'] !== 'gins-trans') {
      // ERROR: Unauthorized
      $this->_sendResponse(401);
      return false;
    }
    $username = $_SERVER['HTTP_X_USERNAME'];
    $password = $_SERVER['HTTP_X_PASSWORD'];
    $password_hash = '$2a$14$02i7qGffGoggpaedshKXzueZq/8XofpqHvUlMP3BwclMnBhSG9R4a';
    if ($password_hash === crypt($password, $password_hash)) {
      $logger->info("passwords match: " . $password_hash . " " . crypt($password, $password_hash));
    } else {
      $logger->info("passwords match: " . $password_hash . " " . crypt($password, $password_hash));
      $this->_sendResponse(401);
      return false;
    }
  }
}


