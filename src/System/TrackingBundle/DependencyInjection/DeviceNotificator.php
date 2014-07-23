<?php
namespace System\TrackingBundle\DependencyInjection;
use System\TrackingBundle\Entity\Device;
use System\TrackingBundle\Entity\Message;
use System\TrackingBundle\Entity\Object;
use RMS\PushNotificationsBundle\Service\Notifications;
use JMS\Serializer\Serializer;
use RMS\PushNotificationsBundle\Message\AndroidMessage;

class DeviceNotificator{ 
    private $notifications;
    private $serializer;
    private $em;
    
    public function __construct(\Doctrine\ORM\EntityManager $em, Notifications $notifications, Serializer $serializer){
        $this->notifications = $notifications;
        $this->serializer = $serializer;
        $this->em = $em;
    }
    
    public function sendNodataCriticalAlert(Device $device, Object $object){
        $this->send($device, 'alert', array(
            'parameters' => array(
                'message' => sprintf('Object %s is not tracked for more than %s minutes!', $object->getName(), $device->getNodataCriticalTimeout()/60),
                'type' => 'critical'
            )
        ));
    }

    public function sendNodataAlert(Device $device, Object $object){
        $this->send($device, 'alert', array(
            'parameters' => array(
                'message' => sprintf('Object %s is not tracked for more than %s minutes!', $object->getName(), $device->getNodataTimeout()/60),
                'type' => 'notice'
            )
        ));
    }
    
    private function send(Device $device, $type, $request){
        // create message
        $message = new Message();
        $message->setDevice($device);
        $message->setDateCreated(new \DateTime());
        $message->setAction($type);
        
        $message->setRequest($this->serializer->serialize($request['parameters'], 'json'));
        
        $this->em->persist($message);
        $this->em->flush();
        
        // add id action
        $request['action'] = $type;
        $request['id']     = $message->getId();
        
        // send message
        $message = new AndroidMessage();
        $message->setGCM(true);
        $message->setMessage($this->serializer->serialize($request, 'json'));
        $message->setDeviceIdentifier($device->getRegId());
         
        $this->notifications->send($message);
    }
}