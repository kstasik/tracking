<?php
namespace System\TrackingBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use System\TrackingBundle\Entity\Position;

class FillPositionCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('tracking:position:fill')
            ->setDescription('Create a user.');
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $db = new \PDO('sqlite:'.realpath(__DIR__.'/../Resources/positions.sql'));
        
        $stmt = $db->prepare('SELECT * FROM positions');
        $stmt->execute();
        
        $em = $this->getContainer()->get('doctrine')->getManager();
        
        $user = $em->getRepository('SystemTrackingBundle:User')->find(1);

        $output->writeln(sprintf('adding positions for <comment>%s</comment>', $user->getUsername()));
        
        while($p = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $object = new Position;
            
            $object
                ->setUser($user)
                ->setLatitude($p['lat'])
                ->setLongitude($p['lng'])
                ->setDateCreated($p['date_created'] ? new \DateTime(date('Y-m-d H:i:s', $p['date_created'])) : null)
                ->setDateSatellite($p['date_satellite'] ? new \DateTime(date('Y-m-d H:i:s', $p['date_satellite'])) : null)
                ->setSpeed($p['speed'])
                ->setAltitude($p['alt'])
                ->setCourse($p['course']);
            
            $em->persist($object);
        }
        
        $em->flush();
    }
}
