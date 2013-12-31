<?php

namespace System\TrackingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('SystemTrackingBundle:Default:index.html.twig', array('name' => 'dupaaa'));
    }
}
