<?php

namespace System\TrackingBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use System\TrackingBundle\Entity\Position;

class TripController extends Controller
{
    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function indexAction($id)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        
        $re = $this->getDoctrine()->getRepository('SystemTrackingBundle:Object');
        if(!($object = $re->find($id))){
            throw $this->createNotFoundException('Object doesn\'t exist');
        }

        if(!$object->getUsers()->contains($user)){
            throw $this->createNotFoundException('You can\'t view someone elses objects!');
        }

        $em = $this->getDoctrine()->getRepository('SystemTrackingBundle:Position');
        return $this->render('SystemTrackingBundle:Trip:index.html.twig', array(
                        'object' => $object,
                        'trips' => $em->getTripCollection($object)));
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function viewAction($id){
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getRepository('SystemTrackingBundle:Position');
        if(!($trip = $em->find($id))){
            throw $this->createNotFoundException('The trip doesn\'t exist');
        }
        
        if(!$trip->getObject()->getUsers()->contains($user)){
            throw $this->createNotFoundException('You can\'t view someone elses objects!');
        }

        return $this->render('SystemTrackingBundle:Trip:view.html.twig', array(
            'positions' => $em->getTripPositionCollection($trip)
        ));
    }
}
