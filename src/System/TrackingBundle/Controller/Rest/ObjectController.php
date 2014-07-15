<?php

namespace System\TrackingBundle\Controller\Rest;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ObjectController extends FOSRestController
{
    /**
     * @Annotations\View(templateVar="object")
     */
    public function getObjectsAllAction(Request $request){
        $device = $this->container->get('system_tracking.device.handler')->getByApiKey($request->get('api_key'));
        
        return $device->getObjects();        
    }
    
    /**
     * @Annotations\View(templateVar="object")
     */
    public function getObjectAction($id){
        $user = $this->get('security.context')->getToken()->getUser();
        
        $object = $this->container->get('system_tracking.object.handler')->get($id);
        
        if(!$object){
            throw new NotFoundHttpException(sprintf('The object with id \'%s\' was not found.',$id));
        }

        if(!$object->getUsers()->contains($user)){
            throw $this->createNotFoundException('You can\'t view someone elses objects!');
        }
        
        return $object;
    }

    /**
     * @Annotations\View(templateVar="object")
     */
    public function getObjectStatusAction($id){
        $user = $this->get('security.context')->getToken()->getUser();
    
        $object = $this->container->get('system_tracking.object.handler')->get($id);
    
        if(!$object){
            throw new NotFoundHttpException(sprintf('The object with id \'%s\' was not found.',$id));
        }
    
        if(!$object->getUsers()->contains($user)){
            throw $this->createNotFoundException('You can\'t view someone elses objects!');
        }
        
        $repo = $this->getDoctrine()->getRepository('SystemTrackingBundle:Position');
        $position = $repo->getLastObjectPosition($object);
    
        return array(
            'position' => $position,
            'status' => $position ? $this->get('system.twig.api.state')->getStatusUsingPosition($position) : \System\TrackingBundle\DependencyInjection\TrackingState::STATUS_NOT_INSTALLED
        );
    }
}