<?php
namespace System\TrackingBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

class PositionRepository extends EntityRepository{
    const TRIPS_INTERVAL = 'INTERVAL 30 MINUTE';
    
    public function getLastPosition(User $user){
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
    
    public function getTripCollection(Object $object){
        return $this->getEntityManager()->createQuery('SELECT a.id, a.date_created '.
            'FROM \System\TrackingBundle\Entity\Position a '.
            'LEFT JOIN \System\TrackingBundle\Entity\Position b '.
            'WITH( b.date_created >= DATETIME_SUB(a.date_created, '.self::TRIPS_INTERVAL.') AND b.date_created < a.date_created AND b.object = :object ) WHERE b.date_created IS NULL AND a.object = :object '.
            'GROUP BY a.date_created ORDER BY a.date_created DESC')
            ->setParameter('object', $object->getId())
            ->getResult();
    }
    
    public function getTripPositionCollection(Position $trip){
        $q = $this->getEntityManager()->createQueryBuilder()
               ->select('p')
               ->from('\System\TrackingBundle\Entity\Position', 'p')
               ->orderBy('p.date_created', 'desc')
               ->where('p.object = :object')
               ->setParameter('object', $trip->getObject()->getId());
        
        try{
            $next = $this->getNextTripId($trip);
            
            return $q->where('p.id >= :from AND p.id < :to')
                   ->setParameters(array(
                        'from' => $trip->getId(),
                        'to' => $next
                   ))->getQuery()->getResult();
        }
        catch(\Doctrine\ORM\NoResultException $e){}
        
        return $q->where('p.id >= ?1')->setParameters(array( 1 => $trip->getId() ) )->getQuery()->getResult();
    }
    
    protected function getNextTripId(Position $trip){
        /* @var $query Doctrine\ORM\Query */
        $query = $this->getEntityManager()->createQuery('SELECT MIN(a.id) '.
                'FROM \System\TrackingBundle\Entity\Position a '.
                'LEFT JOIN \System\TrackingBundle\Entity\Position b '.
                'WITH( b.date_created >= DATETIME_SUB(a.date_created, '.self::TRIPS_INTERVAL.') AND b.date_created < a.date_created AND b.object = :object ) WHERE b.date_created IS NULL AND a.object = :object AND a.id > :start '.
                'GROUP BY a.date_created ORDER BY a.date_created ASC')
                ->setMaxResults(1);
        
        $query
            ->setParameter('start', $trip->getId())
            ->setParameter('object', $trip->getObject()->getId());
        
        return $query->getSingleScalarResult();
    }
}