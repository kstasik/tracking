<?php
namespace System\TrackingBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use System\TrackingBundle\Entity\Position;

class FixPositionDateCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('tracking:position:fix')
            ->setDescription('Fix position dates.');
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        
        $positions = $em->getRepository('SystemTrackingBundle:Position')->findAll();
        foreach($positions as $position){
            $output->writeln(sprintf('fixing <comment>%s</comment>', $position->getDateCreated()->format('Y-m-d H:i:s')));
            
            if($position->getDateSatellite()){
                $time = $position->getDateCreated()->getTimestamp();
                
                $new = $position->getDateSatellite();
                $new->setDate(date('Y', $time), date('m', $time), date('d', $time));
                $position->setDateFixed($new);
            }else{
                $position->setDateFixed($position->getDateCreated());
            }
            
            $em->persist($position);
        }
        
        $em->flush();
    }
}
