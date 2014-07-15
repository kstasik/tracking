<?php

namespace System\TrackingBundle\Controller\Rest;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MessageController extends FOSRestController
{
   
    /**
     * @Annotations\View(templateVar="message")
     *
     * @param Request $request
     * @return array
     */
    public function putMessageAction($id, Request $request){
        $user = $this->get('security.context')->getToken()->getUser();

        $message = $this->getDoctrine()->getRepository('SystemTrackingBundle:Message')->find($id);
        $em = $this->getDoctrine()->getManager();
        
        if(!$message){
            throw new NotFoundHttpException(sprintf('The message with id \'%s\' was not found.',$id));
        }
        
        if($message->getDevice()->getUser() != $user){
            throw $this->createNotFoundException('You can\'t change someone elses messages!');
        }
        
        $message->setResponse($request->get('response'));

        $em->persist($message);
        $em->flush();
        
        return $message;
    }
}