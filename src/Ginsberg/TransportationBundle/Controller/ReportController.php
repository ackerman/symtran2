<?php

namespace Ginsberg\TransportationBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Monolog\Logger;

class ReportController extends Controller {

  /**
   * @Route("/report", name="report")
   * @Template()
   */
  public function reportAction() {
    $logger = $this->get('logger');
    // get the service container to pass to the closure
    $container = $this->container;
    $response = new StreamedResponse(function() use($container) {

      $em = $container->get('doctrine')->getManager();

      // The getExportQuery method returns a query that is used to retrieve
      // all the objects (lines of your csv file) you need. The iterate method
      // is used to limit the memory consumption
      $reservationRepository = $em->getRepository('GinsbergTransportationBundle:Reservation');
      $now = new \DateTime();
      $query = $em->createQuery('SELECT r FROM GinsbergTransportationBundle:Reservation r 
            WHERE r.end < :now AND r.vehicle IS NOT NULL ORDER BY r.start')
            ->setParameters(array('now' => $now,));
      $results = $query->iterate();
      
      //var_dump($results);
      $handle = fopen('php://output', 'r+');

      while (false !== ($row = $results->next())) {
        // add a line in the csv file. You need to implement a toArray() method
        // to transform your object into an array
        fputcsv($handle, $row[0]->toArray());
        // used to limit the memory consumption
        $em->detach($row[0]);
      }

      fclose($handle);
    });

    $response->headers->set('Content-Type', 'application/force-download');
    $response->headers->set('Content-Disposition', 'attachment; filename="export.csv"');

    return $response;
  }
  
  

}
