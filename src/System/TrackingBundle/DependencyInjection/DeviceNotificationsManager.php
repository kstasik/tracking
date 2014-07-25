<?php
namespace System\TrackingBundle\DependencyInjection;
use System\TrackingBundle\Entity\Device;
use System\TrackingBundle\Entity\Message;
use System\TrackingBundle\Entity\Object;
use System\TrackingBundle\Entity\Position;
use RMS\PushNotificationsBundle\Service\Notifications;
use JMS\Serializer\Serializer;
use RMS\PushNotificationsBundle\Message\AndroidMessage;

class DeviceNotificationsManager{ 
    /**
     * Timeout for all devices to respond to position request. In seconds.
     * 
     * @var integer
     */
    const POSITION_NOT_CONFIRMED_TIMEOUT = 600;
    
    private $notifications;
    private $logger;
    private $em;
    
    public function __construct(\Doctrine\ORM\EntityManager $em, DeviceNotificator $notificator){
        $this->notificator = $notificator;
        $this->em = $em;
    }
    
    /**
     * Checks statuses of devices and sends messages
     */
    public function broadcast(){
        // 1. no data send from tracking device
        $this->log('<comment>inform about missing data from objects</comment>');
        
        $dr = $this->em->getRepository('SystemTrackingBundle:Device');
        $pr = $this->em->getRepository('SystemTrackingBundle:Position');
        
        $devices = $dr->findWithNoDataAlerts();
        foreach($devices as $device){
            $this->log('+----+ checking %s device', $device->getName());
        
            foreach($device->getObjects() as $object){
                 $this->log('     +---- checking %s object', $object->getName());
        
                $position = $pr->getLastObjectPosition($object);
        
                $this->log('     +---- last position: %s', $position->getDateFixed()->format('Y-m-d H:i:s'));
        
                if($device->getNodataCriticalTimeout() && $device->getNodataCriticalTimeout() < time()-$position->getDateFixed()->getTimestamp()){
                    $this->log('     +---- <error>critical timeout!</error>');
        
                    if(!$this->notificator->sendNodataCriticalAlert($device, $object)){
                        $this->log('     +---- notification already sent');
                    }
                }
                elseif($device->getNodataTimeout() && $device->getNodataTimeout() < time()-$position->getDateFixed()->getTimestamp()){
                    $this->log('     +---- <comment>normal timeout!</comment>');
        
                    if(!$this->notificator->sendNodataAlert($device, $object)){
                        $this->log('     +---- notification already sent');
                    }
                }
                elseif($device->getNodataTimeout() || $device->getNodataCriticalTimeout()){
                    $this->log('     +---- device online');
                }
                else{
                    $this->log('     +---- notification disabled');
                }
            }
        }
        
        // 2. trip begins - check which device is near object and if there is no device - send alerts!
        $this->log('<comment>check which device is near object when trip starts</comment>');
        $mr = $this->em->getRepository('SystemTrackingBundle:Message');
        
        $devices = $dr->findWithAlertsEnabled();
        foreach($devices as $device){
            $this->log('+----+ checking %s device', $device->getName());
        
            foreach($device->getObjects() as $object){
                $this->log('     +---- checking %s object', $object->getName());
        
                // check trip begining and while it is moving every X seconds if device is still near the object.
                $positions = $pr->getLastTripPositions($object, 15*60);
        
                foreach($positions as $position){
                    if(!$this->notificator->sendIsNearMessages($device, $position)){
                        $this->log('     +---- message to confirm position already sent', $device->getName());
                        $messages = $mr->getNearObjectMessages($position);
        
                        if($devices = $mr->getDevices($messages)){
                            // attach devices to position
                            foreach($devices as $device){
                                $this->log('     +---- device %s detected near object', $device->getName());
                            }
                        }
                        else{
                            if($mr->areAllMessagesAnswered($messages)){
                                $this->log('     +---- all messages answered');
                                $this->log('     +---- <error>sending alert to device</error>');
        
                                // send alert that object is moving and noone is near it
                                if(!$this->notificator->sendNoDeviceNearPosition($device, $position)){
                                    $this->log('     +---- alert already sent');
                                }
                            }
                            elseif(time()-$messages->first()->getDateCreated()->getTimestamp() > self::POSITION_NOT_CONFIRMED_TIMEOUT){
                                $this->log('     +---- notifications timeout - no device confirmed that is near the object (in 10 minutes)');
                                $this->log('     +---- <error>sending alert to device</error>');
        
                                // send alert that object is moving and noone is near it
                                if(!$this->notificator->sendNoDeviceNearPosition($device, $position)){
                                    $this->log('     +---- alert already sent');
                                }
                            }else{
                                $this->log('     +---- still waiting for response about positions of all devices');
                            }
                        }
                    }
                    else{
                        $this->log('     +---- sending message to confirm position...', $device->getName());
                    }
                }
            }
        }
    }
    
    /**
     * Sets custom logger - for debugging purposes 
     * 
     * @param Callback $callback
     */
    public function setLogger($callback){
        $this->logger = $callback;
    }
    
    /**
     * Pushes message to through the logger
     * 
     * @return boolean
     */
    public function log(){
        if(!$this->logger)
            return false;
        
        call_user_func($this->logger, call_user_func_array('sprintf', func_get_args()));
        
        return true;
    }
}