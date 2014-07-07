<?php

namespace System\TrackingBundle\Controller\Rest;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DeviceController extends FOSRestController
{
    /**
     * @Annotations\View(templateVar="device")
     * 
     * @param Request $request
     * @return array
     */
    public function postDeviceAction(Request $request)
    {
        try {
            $device = $this->container->get('system_tracking.device.handler')->post(
                $request->request->all()
            );
        
            $routeOptions = array(
                'id' => $device->getId(),
                '_format' => $request->get('_format')
            );
        
            return $device;
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }
    }
}