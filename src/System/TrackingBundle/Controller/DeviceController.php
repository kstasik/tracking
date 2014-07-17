<?php

namespace System\TrackingBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use System\TrackingBundle\Entity\Position;

class DeviceController extends Controller
{
    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function indexAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getRepository('SystemTrackingBundle:Device');

        return $this->render('SystemTrackingBundle:Device:index.html.twig', array('devices' => $em->getDeviceCollection($user)));
    }
}
