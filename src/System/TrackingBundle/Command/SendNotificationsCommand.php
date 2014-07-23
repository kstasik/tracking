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
        // two types of notifications:
        // 1. no data
        // 2. object moving and no device is near it
        
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
                    
                    $this->getContainer()->get('system.device.notificator')->sendNodataCriticalAlert($device, $object);
                }
                elseif($device->getNodataTimeout() && $device->getNodataTimeout() < time()-$position->getDateFixed()->getTimestamp()){
                    $output->writeln(sprintf('     +---- <comment>normal timeout!</comment>'));

                    $this->getContainer()->get('system.device.notificator')->sendNodataAlert($device, $object);
                }
                elseif($device->getNodataTimeout() || $device->getNodataCriticalTimeout()){
                    $output->writeln('     +---- device online');
                }
                else{
                    $output->writeln('     +---- notification disabled');
                }
            }
        }
        
        // get devices with nodata_timeout
        // check objects last od
        

        $output->writeln('<comment>confirm location of devices</comment>');
    }
}
