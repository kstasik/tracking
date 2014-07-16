<?php
namespace System\TrackingBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\Common\Util\Debug;

class PositionRepository extends EntityRepository{
    //const TRIPS_INTERVAL = 'INTERVAL 20 MINUTE';
    
    const RADIUS = 0.05;
    
    const PARKING_THRESHOLD = 3;
    
    public function getLastUserPosition(User $user){        
        $query = $this->getEntityManager()->createQuery('SELECT a FROM \System\TrackingBundle\Entity\Position a, \System\TrackingBundle\Entity\Object o LEFT JOIN o.users u WHERE o.id = a.object AND u.id = :user ORDER BY a.date_created DESC')
            ->setMaxResults(1)
            ->setParameter('user', $user->getId());
        
        try{
            return $query->getSingleResult();
        }
        catch(NoResultException $e){
            return null;
        }        
    }

    public function getLastObjectPosition(Object $object){
        $query = $this->getEntityManager()->createQuery('SELECT a FROM \System\TrackingBundle\Entity\Position a, \System\TrackingBundle\Entity\Object o WHERE o.id = a.object AND a.object = :object ORDER BY a.date_created DESC')
        ->setMaxResults(1)
        ->setParameter('object', $object->getId());
    
        try{
            return $query->getSingleResult();
        }
        catch(NoResultException $e){
            return null;
        }
    }
    
    public function getTripCollection(Object $object){
        return $this->getEntityManager()->createQuery('SELECT a.id, a.date_fixed FROM \System\TrackingBundle\Entity\Position a WHERE a.type = :type AND a.object = :object ORDER BY a.date_fixed DESC')
            ->setParameter('object', $object->getId())
            ->setParameter('type', Position::TYPE_TRIP_START)
            ->getResult();
    }
    
    public function getTripPositionCollection(Position $trip){
        $q = $this->getEntityManager()->createQueryBuilder()
               ->select('p')
               ->from('\System\TrackingBundle\Entity\Position', 'p')
               ->orderBy('p.date_fixed', 'desc')
               ->where('p.object = :object')
               ->setParameter('object', $trip->getObject()->getId());

        try{
            $next = $this->getEndTripDate($trip);
            
            return $q->where('p.date_fixed >= :from AND p.date_fixed <= :to')
                   ->setParameters(array(
                        'from' => $trip->getDateFixed()->format('Y-m-d H:i:s'),
                        'to' => $next
                   ))->getQuery()->getResult();
        }
        catch(\Doctrine\ORM\NoResultException $e){}
        
        return $q->where('p.date_fixed >= ?1')->setParameters(array( 1 => $trip->getDateFixed()->format('Y-m-d H:i:s') ) )->getQuery()->getResult();
    }
    
    protected function getEndTripDate(Position $trip){
        /* @var $query Doctrine\ORM\Query */
        $query = $this->getEntityManager()->createQuery('SELECT MIN(a.date_fixed) FROM \System\TrackingBundle\Entity\Position a WHERE a.object = :object AND a.type = :type AND a.date_fixed > :start')
                ->setMaxResults(1);
        
        $query
            ->setParameter('start', $trip->getDateFixed()->format('Y-m-d H:i:s'))
            ->setParameter('type', Position::TYPE_TRIP_END)
            ->setParameter('object', $trip->getObject()->getId());
        
        return $query->getSingleScalarResult();
    }
    
    /**
     * Classify positions of an object
     * 
     * @param Object $object
     */
    public function classifyByObject(Object $object){
        // save checked positions into the array
        $positions = $this->getEntityManager()->createQuery('SELECT a.id FROM \System\TrackingBundle\Entity\Position a WHERE a.type = :type AND a.object = :object ORDER BY a.date_fixed ASC')
                    ->setParameter('object', $object->getId())
                    ->setParameter('type', Position::TYPE_NEW)
                    ->getResult();
        
        // check position one by one
        foreach($positions as $position){
            /* @var $checked Position */
            $checked = $this->find($position['id']);
            
            // fire position classification
            $this->classify($checked);
        }       
    }
    
    public function getSurroundings(Position $position){
        $before = $this->getEntityManager()->createQuery('SELECT a FROM \System\TrackingBundle\Entity\Position a WHERE a.date_fixed <= :date AND a.id != :position AND a.object = :object ORDER BY a.date_fixed DESC')
            ->setParameter('object', $position->getObject()->getId())
            ->setParameter('date', $position->getDateFixed())
            ->setParameter('position', $position->getId())
            ->setMaxResults(10)
            ->getResult(Query::HYDRATE_OBJECT);
        
        $after = $this->getEntityManager()->createQuery('SELECT a FROM \System\TrackingBundle\Entity\Position a WHERE a.date_fixed >= :date AND a.id != :position AND a.object = :object ORDER BY a.date_fixed ASC')
            ->setParameter('object', $position->getObject()->getId())
            ->setParameter('date', $position->getDateFixed())
            ->setParameter('position', $position->getId())
            ->setMaxResults(10)
            ->getResult(Query::HYDRATE_OBJECT);
        
        return array_merge( array_reverse($before), array($position), $after );
    }
    
    public function classify(Position $position){
        $em = $this->getEntityManager();
        
        $previous = null;
        $parking  = null;
        $group    = array();
        $context  = $this->getSurroundings($position);
        
        foreach($context as $inspected){
            // group position in parking mode
            if($previous != null){
                $distance = $this->getDistance($inspected, $previous);

                if($distance < self::RADIUS){
                    if($parking){
                        // if distance from parking context is less then radius
                        if($this->getDistance($inspected, $parking) < self::RADIUS ){
                            // set inspected element to parking cadidate
                            $inspected->setType(Position::TYPE_PARKING_CANDIDATE);
                        }
                        // reset parking context (deactivate)
                        // and set inspected element to trip position
                        else{
                            $parking = null;
                            $inspected->setType(Position::TYPE_TRIP);
                        }
                    }
                    else{
                        if($previous->getType() != Position::TYPE_TRIP_END)
                            $previous->setType(Position::TYPE_PARKING_CONTEXT);
                        
                        $parking = $previous;
                        $inspected->setType(Position::TYPE_PARKING_CANDIDATE);
                    }
                }else{
                    // set inspected element to trip
                    $parking = null;
                    $inspected->setType(Position::TYPE_TRIP);
                }
            }
            elseif($inspected->getType() == Position::TYPE_NEW){
                // examined position doesnt have previous element and first one is new
                // set it to trip by defauly
                $inspected->setType(Position::TYPE_TRIP);
            }
            elseif($inspected->getType() == Position::TYPE_PARKING){
                $parking = $inspected;
                $group = null;
            }
            
            // parking threshold - number of parking position (without parking context) in a row
            if($group === null){
                // if first element is already set to TYPE_PARKING it means
                // that we don't want to apply threshold filter until trip position is found
                if($inspected->getType() == Position::TYPE_TRIP){
                    $group = array();
                }
            }
            else{
                if(in_array($inspected->getType(), array(Position::TYPE_PARKING_CONTEXT, Position::TYPE_PARKING_CANDIDATE))){
                    if($parking && !in_array($parking, $group)){
                        $group[] = $parking;
                    }
                    
                    $group[] = $inspected;
                }
                else{
                    if(count($group) < self::PARKING_THRESHOLD){
                        foreach($group as $_element){
                            $_element->setType(Position::TYPE_TRIP);
                        }
                    }
                        
                    $group = array();
                }
            }
            
            // set previous
            $previous = $inspected;
        }
        
        $previous = null;
        foreach($context as $element){
            if(in_array($element->getType(),array(Position::TYPE_PARKING_CONTEXT,Position::TYPE_PARKING_CANDIDATE))){
                $element->setType(Position::TYPE_PARKING);
            }
            
            if($previous){
                if($previous->getType() == Position::TYPE_PARKING && $element->getType() == Position::TYPE_TRIP){
                    $previous->setType(Position::TYPE_TRIP_START);
                }
                else if($previous->getType() == Position::TYPE_TRIP && $element->getType() == Position::TYPE_PARKING){
                    $element->setType(Position::TYPE_TRIP_END);
                }
            }
            
            $em->persist($element);
            $previous = $element;
        }
        
        $em->flush();
    }
    
    public function getDistance(Position $prev, Position $next) {
        $theta = $prev->getLongitude() - $next->getLongitude();
        
        $dist = sin(deg2rad($prev->getLatitude())) * sin(deg2rad($next->getLatitude())) +  
                cos(deg2rad($prev->getLatitude())) * cos(deg2rad($next->getLatitude())) * cos(deg2rad($theta));

        return rad2deg(acos($dist)) * 60 * 1.853159616;
      }
}