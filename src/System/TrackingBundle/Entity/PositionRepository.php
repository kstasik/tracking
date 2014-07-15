<?php
namespace System\TrackingBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;

class PositionRepository extends EntityRepository{
    const TRIPS_INTERVAL = 'INTERVAL 20 MINUTE';
    
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
        return $this->getEntityManager()->createQuery('SELECT a.id, a.date_fixed '.
            'FROM \System\TrackingBundle\Entity\Position a '.
            'LEFT JOIN \System\TrackingBundle\Entity\Position b '.
            'WITH( b.date_fixed >= DATETIME_SUB(a.date_fixed, '.self::TRIPS_INTERVAL.') AND b.date_fixed < a.date_fixed AND b.object = :object ) WHERE b.date_fixed IS NULL AND a.object = :object '.
            'GROUP BY a.date_fixed ORDER BY a.date_fixed DESC')
            ->setParameter('object', $object->getId())
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
            $next = $this->getNextTripDate($trip);
            
            return $q->where('p.date_fixed >= :from AND p.date_fixed < :to')
                   ->setParameters(array(
                        'from' => $trip->getDateFixed()->format('Y-m-d H:i:s'),
                        'to' => $next
                   ))->getQuery()->getResult();
        }
        catch(\Doctrine\ORM\NoResultException $e){}
        
        return $q->where('p.date_fixed >= ?1')->setParameters(array( 1 => $trip->getDateFixed()->format('Y-m-d H:i:s') ) )->getQuery()->getResult();
    }
    
    protected function getNextTripDate(Position $trip){
        /* @var $query Doctrine\ORM\Query */
        $query = $this->getEntityManager()->createQuery('SELECT MIN(a.date_fixed) '.
                'FROM \System\TrackingBundle\Entity\Position a '.
                'LEFT JOIN \System\TrackingBundle\Entity\Position b '.
                'WITH( b.date_fixed >= DATETIME_SUB(a.date_fixed, '.self::TRIPS_INTERVAL.') AND b.date_fixed < a.date_fixed AND b.object = :object ) WHERE b.date_fixed IS NULL AND a.object = :object AND a.date_fixed > :start '.
                'GROUP BY a.date_fixed ORDER BY a.date_fixed ASC')
                ->setMaxResults(1);
        
        $query
            ->setParameter('start', $trip->getDateFixed()->format('Y-m-d H:i:s'))
            ->setParameter('object', $trip->getObject()->getId());
        
        return $query->getSingleScalarResult();
    }
}