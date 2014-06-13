<?php

namespace System\TrackingBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TripController extends Controller
{
    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function indexAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getRepository('SystemTrackingBundle:Position');

        return $this->render('SystemTrackingBundle:Trip:index.html.twig', array('trips' => $em->getTripCollection($user)));
    }
    
    public function viewAction($id){
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getRepository('SystemTrackingBundle:Position');
        if(!($trip = $em->find($id))){
            throw $this->createNotFoundException('The trip doesn\'t exist');
        }

        return $this->render('SystemTrackingBundle:Trip:view.html.twig', array(
            'positions' => $em->getTripPositionCollection($user, $trip)
        ));
    }
}
