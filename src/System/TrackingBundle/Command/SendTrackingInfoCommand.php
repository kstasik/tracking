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

class SendTrackingInfoCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('tracking:send')
            ->setDescription('Send information to mobiel devices.');
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<comment>send gcm and ios notifications</comment>');        
        
        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();
        
        $devices = $doctrine->getRepository('SystemTrackingBundle:Device')->findAll();
        $repo    = $doctrine->getRepository('SystemTrackingBundle:Position');

        foreach($devices as $device){
            $output->writeln(sprintf('sending message to device %d', $device->getId()));

            $position = $repo->getLastObjectPosition($device->getObjects()->first());
            
            // create request
            $request = array(
                'parameters' => array(
                    'position' => array(
                	   'latitude' => $position->getLatitude(),
                	   'longitude' => $position->getLongitude(),
                    )      
                )
            );
            
            // create message
            $message = new Message();
            $message->setDevice($device);
            $message->setDateCreated(new \DateTime());
            $message->setAction('is_near_object');
            
            $message->setRequest($this->getContainer()->get('jms_serializer')->serialize($request['parameters'], 'json'));
            
            $em->persist($message);
            $em->flush();
            
            // add id action
            $request['action'] = 'is_near_object';
            $request['id']     = $message->getId();
            
            // send message
            $message = new AndroidMessage();
            $message->setGCM(true);
            $message->setMessage($this->getContainer()->get('jms_serializer')->serialize($request, 'json'));
            $message->setDeviceIdentifier($device->getRegId());
               
            $this->getContainer()->get('rms_push_notifications')->send($message);
        }       
    }
}
