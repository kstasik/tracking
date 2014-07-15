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
        
            return $device;
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }
    }

    /**
     * @Annotations\View(templateVar="device")
     */
    public function getDevicesCurrentAction(Request $request){
        $user = $this->get('security.context')->getToken()->getUser();
        
        $device = $this->container->get('system_tracking.device.handler')->getByApiKey($request->get('api_key'));
        
        if(!$device){
            throw new NotFoundHttpException(sprintf('Device attached to this api key not found.'));
        }
        
        return $device;
    }
    
    /**
     * @Annotations\View(templateVar="device")
     */
    public function getDeviceAction($id, Request $request){
        $user = $this->get('security.context')->getToken()->getUser();
        
        $device = $this->container->get('system_tracking.device.handler')->get($id);
        
        if(!$device){
            throw new NotFoundHttpException(sprintf('The device with id \'%s\' was not found.',$id));
        }

        if($device->getUser() != $user){
            throw $this->createNotFoundException('You can\'t view someone elses devices!');
        }
        
        return $device;
    }
}