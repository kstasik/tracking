<?php

namespace System\TrackingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('SystemTrackingBundle:Default:index.html.twig');
    }
    
    /**
     * Redirect to default language directory
     */
	public function homeAction()
    {
        return $this->redirect($this->generateUrl('system_tracking_homepage'), 301);
    }	
}
