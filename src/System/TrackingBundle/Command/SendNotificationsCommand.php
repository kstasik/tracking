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
        $manager = $this->getContainer()->get('system.device.notificationmanager');
        
        $manager->setLogger(function($message) use ($output){ 
            $output->writeln($message);
        });
        
        $manager->broadcast();
        
        $output->writeln('broadcasting notifications finished');
    }
}
