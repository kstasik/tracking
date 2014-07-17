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
            ->addArgument(
                'type',
                InputArgument::REQUIRED,
                'Type of message: [is_near_object,alert]?'
            )
            ->setDescription('Send information to mobiel devices.');
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getArgument('type');
        
        if(!in_array($type, array('is_near_object','alert'))){
            $output->writeln(sprintf('<error>wrong message type: %s</error>', $type));
            return;
        }
        
        $output->writeln(sprintf('message type: <comment>%s</comment>', $type));
        $output->writeln('<comment>send gcm and ios notifications</comment>');        
        
        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();
        
        $devices = $doctrine->getRepository('SystemTrackingBundle:Device')->findAll();
        $repo    = $doctrine->getRepository('SystemTrackingBundle:Position');

        foreach($devices as $device){
            $output->writeln(sprintf('sending message to device %d', $device->getId()));

            $position = $repo->getLastObjectPosition($device->getObjects()->first());

            $request = array(
                'parameters' => null
            );
            
            // create request
            if($type == 'is_near_object'){
                $request['parameters'] = array(
                    'position' => array(
                	   'latitude' => $position->getLatitude(),
                	   'longitude' => $position->getLongitude(),
                    ) 
                );
            }
            elseif($type == 'alert'){
                $request['parameters'] = array(
                    'message' => 'Object is in the move and no device is near it!' 
                );
            }
            
            // create message
            $message = new Message();
            $message->setDevice($device);
            $message->setDateCreated(new \DateTime());
            $message->setAction($type);
            
            $message->setRequest($this->getContainer()->get('jms_serializer')->serialize($request['parameters'], 'json'));
            
            $em->persist($message);
            $em->flush();
            
            // add id action
            $request['action'] = $type;
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
