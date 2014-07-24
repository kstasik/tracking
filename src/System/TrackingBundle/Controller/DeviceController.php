<?php

namespace System\TrackingBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use System\TrackingBundle\Entity\Position;
use System\TrackingBundle\Form\SecondsType;

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

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function editAction($id, Request $request){
        $user = $this->get('security.context')->getToken()->getUser();
    
        $device = $this->getDoctrine()->getRepository('SystemTrackingBundle:Device')->find($id);
    
        if (!$device || $device->getUser() != $user) {
            throw $this->createNotFoundException('Device does not exist');
        }
    
        $form = $this->createFormBuilder($device)
            ->add('name', 'text')
            ->add('alerts_enabled', 'checkbox', array('required'  => false))
            ->add('nodata_timeout', new SecondsType(), array('required'  => false))
            ->add('nodata_critical_timeout', new SecondsType(), array('required'  => false))
            ->add('save', 'submit')
            ->add('objects', 'entity', array(
                'class' => 'System\TrackingBundle\Entity\Object',
                'query_builder' => function(\System\TrackingBundle\Entity\ObjectRepository $or) use($user) {
                    return $or->findByUserQB($user);
                },
                'property' => 'name',
                'multiple' => true,
                'expanded' => true
            ))
            ->getForm();
    
        if($request->isMethod('POST')){
            $form->submit($request);
    
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
    
                $em->persist($device);
                $em->flush();
    
                return $this->redirect($this->generateUrl('system_tracking_device'));
            }
        }
        
        return $this->render('SystemTrackingBundle:Device:edit.html.twig', array(
                        'form' => $form->createView(),
                        'api_key' => $device->getApiKey(),
                        'messages' => $this->getDoctrine()->getRepository('SystemTrackingBundle:Message')->getLastMessagesCollection($device)
        ));
    }

    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function deleteAction($id, Request $request){
        $user = $this->get('security.context')->getToken()->getUser();
    
        // create a task and give it some dummy data for this example
        $device = $this->getDoctrine()->getRepository('SystemTrackingBundle:Device')->find($id);
    
        if (!$device || $device->getUser() != $user) {
            throw $this->createNotFoundException('Device does not exist');
        }
    
        $form = $this->createFormBuilder($device)
            ->add('save', 'submit')
            ->getForm();
    
        if($request->isMethod('POST')){
            $form->submit($request);
    
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($device);
                $em->flush();
    
                return $this->redirect($this->generateUrl('system_tracking_device'));
            }
        }
    
        return $this->render('SystemTrackingBundle:Device:delete.html.twig', array(
                        'form' => $form->createView(),
                        'device' => $device
        ));
    }
}
