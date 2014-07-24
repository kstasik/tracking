<?php
namespace System\TrackingBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use System\TrackingBundle\Entity\Position;
use System\TrackingBundle\Entity\Message;
use RMS\PushNotificationsBundle\Message\AndroidMessage;

class SendNotificationsCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('tracking:notifications')
            ->setDescription('Send alerts and confirm mobile devices location near object.');
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // 1. no data send from tracking device
        $output->writeln('<comment>inform about missing data from objects</comment>');  

        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();
        $dr = $doctrine->getRepository('SystemTrackingBundle:Device');
        $pr = $doctrine->getRepository('SystemTrackingBundle:Position');
        
        $devices = $dr->findWithNoDataAlerts();
        foreach($devices as $device){
            $output->writeln(sprintf('+----+ checking %s device', $device->getName()));
            
            foreach($device->getObjects() as $object){
                $output->writeln(sprintf('     +---- checking %s object', $object->getName()));
                
                $position = $pr->getLastObjectPosition($object);
                
                $output->writeln(sprintf('     +---- last position: %s', $position->getDateFixed()->format('Y-m-d H:i:s')));
                
                if($device->getNodataCriticalTimeout() && $device->getNodataCriticalTimeout() < time()-$position->getDateFixed()->getTimestamp()){
                    $output->writeln(sprintf('     +---- <error>critical timeout!</error>'));
                    
                    if(!$this->getContainer()->get('system.device.notificator')->sendNodataCriticalAlert($device, $object)){
                        $output->writeln(sprintf('     +---- notification already sent'));
                    }
                }
                elseif($device->getNodataTimeout() && $device->getNodataTimeout() < time()-$position->getDateFixed()->getTimestamp()){
                    $output->writeln(sprintf('     +---- <comment>normal timeout!</comment>'));

                    if(!$this->getContainer()->get('system.device.notificator')->sendNodataAlert($device, $object)){
                        $output->writeln(sprintf('     +---- notification already sent'));
                    }
                }
                elseif($device->getNodataTimeout() || $device->getNodataCriticalTimeout()){
                    $output->writeln('     +---- device online');
                }
                else{
                    $output->writeln('     +---- notification disabled');
                }
            }
        }

        // 2. trip begins - check which device is near object and if there is no device - send alerts!
        $output->writeln('<comment>check which device is near object when trip starts</comment>');

        $mr = $doctrine->getRepository('SystemTrackingBundle:Message');
        
        $devices = $dr->findWithAlertsEnabled();
        foreach($devices as $device){
            $output->writeln(sprintf('+----+ checking %s device', $device->getName()));
        
            foreach($device->getObjects() as $object){
                $output->writeln(sprintf('     +---- checking %s object', $object->getName()));
        
                // check trip begining and while it is moving every X seconds if device is still near the object.
                $positions = $pr->getLastTripPositions($object, 15*60);
        
                foreach($positions as $position){
                    if(!$this->getContainer()->get('system.device.notificator')->sendIsNearMessages($device, $position)){
                        $output->writeln(sprintf('     +---- message to confirm position already sent', $device->getName()));
                        $messages = $mr->getNearObjectMessages($position);
                        
                        if($devices = $mr->getDevices($messages)){
                            // attach devices to position
                            foreach($devices as $device){
                                $output->writeln(sprintf('     +---- device %s detected near object', $device->getName()));
                            }
                        }
                        else{
                            if($mr->areAllMessagesAnswered($messages)){
                                $output->writeln(sprintf('     +---- all messages answered'));
                                $output->writeln(sprintf('     +---- <error>sending alert to device</error>'));
                                
                                // send alert that object is moving and noone is near it
                                if(!$this->getContainer()->get('system.device.notificator')->sendNoDeviceNearPosition($device, $position)){
                                    $output->writeln(sprintf('     +---- alert already sent'));
                                }
                            }
                            elseif(time()-$messages->first()->getDateCreated()->getTimestamp() > 60*10){
                                $output->writeln(sprintf('     +---- notifications timeout - no device confirmed that is near the object (in 10 minutes)'));
                                $output->writeln(sprintf('     +---- <error>sending alert to device</error>'));
                                
                                // send alert that object is moving and noone is near it
                                if(!$this->getContainer()->get('system.device.notificator')->sendNoDeviceNearPosition($device, $position)){
                                    $output->writeln(sprintf('     +---- alert already sent'));
                                }
                            }else{
                                $output->writeln(sprintf('     +---- still waiting for response about positions of all devices'));
                            }
                        }
                    }
                    else{
                        $output->writeln(sprintf('     +---- sending message to confirm position...', $device->getName()));
                    }
                }
            }
        }
    }
}
