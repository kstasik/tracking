<?php
namespace System\TrackingBundle\DependencyInjection;
use System\TrackingBundle\Entity\Device;
use System\TrackingBundle\Entity\Message;
use System\TrackingBundle\Entity\Object;
use System\TrackingBundle\Entity\Position;
use RMS\PushNotificationsBundle\Service\Notifications;
use JMS\Serializer\Serializer;
use RMS\PushNotificationsBundle\Message\AndroidMessage;

class DeviceNotificator{ 
    const RETRY = 7200; // send again after 2 hours
    const CRITICAL_RETRY = 300; // send again after 5 minutes
    
    private $notifications;
    private $serializer;
    private $em;
    
    public function __construct(\Doctrine\ORM\EntityManager $em, Notifications $notifications, Serializer $serializer){
        $this->notifications = $notifications;
        $this->serializer = $serializer;
        $this->em = $em;
    }
    
    private function getLastMessage($criteria){
        return $this->em->getRepository('SystemTrackingBundle:Message')->findOneBy($criteria,  array('id' => 'DESC'));
    }
    
    public function sendNoDeviceNearPosition(Device $device, Position $position){
        $last = $this->getLastMessage(array('object' => $position->getObject(), 'position' => $position, 'device' => $device, 'action' => 'alert_in_move'));
        
        if($last && time()-$last->getDateCreated()->getTimestamp() < self::CRITICAL_RETRY){
            return false;
        }
        
        $this->send($device, 'alert_in_move', array(
            'parameters' => array(
                'message' => sprintf('Object %s is in the move and noone is near it!', $position->getObject()->getName()),
                'type' => 'critical'
                )
            ),
            $position->getObject(),
            $position
        );
        
        return true;
    }

    public function sendIsNearMessages(Device $device, Position $position){
        if($this->getLastMessage(array('position' => $position, 'device' => $device, 'action' => 'is_near_object'))){
            return false;
        }
        
        $this->send($device, 'is_near_object', array(
                'parameters' => array(
                    'position' => array(
                    	   'latitude' => $position->getLatitude(),
                    	   'longitude' => $position->getLongitude(),
                        ) 
                )
            ),
            $position->getObject(),
            $position
        );
        
        return true;
    }
    
    public function sendNodataCriticalAlert(Device $device, Object $object){
        $last = $this->getLastMessage(array('object' => $object, 'device' => $device, 'action' => 'alert_critical'));
        
        if($last && time()-$last->getDateCreated()->getTimestamp() < self::RETRY){
            return false;
        }
        
        $this->send($device, 'alert_critical', array(
            'parameters' => array(
                'message' => sprintf('Object %s is not tracked for more than %s minutes!', $object->getName(), $device->getNodataCriticalTimeout()/60),
                'type' => 'critical'
                )
            ),
            $object
        );
        
        return true;
    }

    public function sendNodataAlert(Device $device, Object $object){
        $last = $this->getLastMessage(array('object' => $object, 'device' => $device, 'action' => 'alert'));        

        if($last && time()-$last->getDateCreated()->getTimestamp() < self::RETRY){
            return false;    
        }
        
        $this->send($device, 'alert', array(
            'parameters' => array(
                'message' => sprintf('Object %s is not tracked for more than %s minutes!', $object->getName(), $device->getNodataTimeout()/60),
                'type' => 'notice'
                )
            ),
            $object
        );
        
        return true;
    }
    
    private function send(Device $device, $type, $request, Object $object = null, Position $position = null){
        // create message
        $message = new Message();
        $message->setDevice($device);
        $message->setDateCreated(new \DateTime());
        $message->setAction($type);
        $message->setObject($object);
        $message->setPosition($position);
                
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