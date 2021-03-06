<?php

namespace Ginsberg\TransportationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\StreamedResponse;
use APY\DataGridBundle\Grid\Source\Entity;
use APY\DataGridBundle\Grid\Export\PHPExcel2007Export;
use APY\DataGridBundle\Grid\Export\CSVExport;
use Monolog\Logger;

/**
 * Report controller.
 *
 * @Route("/report")
 */
class ReportController extends Controller {

  /**
   * Lists all past reservations.
   *
   * @Route("/", name="report")
   * @Method("GET")
   * @Template()
   */
  public function indexAction()
  {
    $logger = $this->get('logger');
    
    $em = $this->getDoctrine()->getManager();

    // Find all past Reservations, including info on paid and unpaid Tickets.
    $now = new \DateTime();
    $reservationRepository = $em->getRepository('GinsbergTransportationBundle:Reservation');
    $allPastReservations = $reservationRepository->findAllPastReservations($now);
    $ticketRepository = $em->getRepository('GinsbergTransportationBundle:Ticket');
    $reservationsWhereDriverHasTicket = array();

    return array(
      'allPastReservations' => $allPastReservations,
      'reservationsWhereDriverHasTicket' => $reservationsWhereDriverHasTicket,
    );
  }
    
  /**
   * @Route("/download", name="report_download")
   * @Template()
   */
  public function downloadAction() {
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
    
    $filename = 'Ginsberg_Transportation_Report_' . date('Y-m-d_H_i', time()) . '.csv';
    
    $response->headers->set('Content-Type', 'application/force-download');
    $response->headers->set('Content-Disposition', "attachment; filename=$filename");

    return $response;
  }
  
  /**
   * @Route("/grid", name="report_grid")
   * @Template()
   */
  public function gridAction() {
    $logger = $this->get('logger');
    // get the service container to pass to the closure
    $source = new Entity('GinsbergTransportationBundle:Reservation');
    $grid = $this->get('grid');
    $grid->setSource($source);
    
    // When default limits are used, we were getting errors about nonexistent 
    // because it was trying to use paging. Setting an arbitrarily high number 
    // was helping, but the problem seems to have gone away. Hopefully.
    //$grid->setLimits(100);
    
    // We are now using force-download in the header, so these are no longer used
    //$title = 'Ginsberg Transportation Report';
    //$fileName = 'Ginsberg_Transportation_Export';
    
    $grid->addExport(new CSVExport('CSV Export'));
    
    $grid->isReadyForRedirect();
    return $grid->getGridResponse('GinsbergTransportationBundle:Report:report.html.twig');
    
    // Left over from attempt to use APY grid bundle, which is what we really should be using here.
    //return $this->render('GinsbergTransportationBundle:Report:report.html.twig', array('grid' => $grid));
  }
  
  

}
