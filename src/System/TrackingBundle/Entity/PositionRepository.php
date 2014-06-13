<?php
namespace System\TrackingBundle\Entity;

use Doctrine\ORM\EntityRepository;

class PositionRepository extends EntityRepository{
    const TRIPS_INTERVAL = 'INTERVAL 30 MINUTE';
    
    public function getTripCollection(User $user){
        return $this->getEntityManager()->createQuery('SELECT a.id, a.date_created '.
            'FROM \System\TrackingBundle\Entity\Position a '.
            'LEFT JOIN \System\TrackingBundle\Entity\Position b '.
            'WITH( b.date_created >= DATETIME_SUB(a.date_created, '.self::TRIPS_INTERVAL.') AND b.date_created < a.date_created AND b.user = :user ) WHERE b.date_created IS NULL AND a.user = :user '.
            'GROUP BY a.date_created ORDER BY a.date_created DESC')
            ->setParameter('user', $user->getId())
            ->getResult();
    }
    
    public function getTripPositionCollection(User $user, Position $trip){
        $q = $this->getEntityManager()->createQueryBuilder()
               ->select('p')
               ->from('\System\TrackingBundle\Entity\Position', 'p')
               ->orderBy('p.date_created', 'desc')
               ->where('p.user = :user')
               ->setParameter('user', $user->getId());
        
        try{
            $next = $this->getNextTripId($user, $trip);
            
            return $q->where('p.id >= :from AND p.id < :to')
                   ->setParameters(array(
                        'from' => $trip->getId(),
                        'to' => $next
                   ))->getQuery()->getResult();
        }
        catch(\Doctrine\ORM\NoResultException $e){}
        
        return $q->where('p.id >= ?1')->setParameters(array( 1 => $trip->getId() ) )->getQuery()->getResult();
    }
    
    protected function getNextTripId(User $user, Position $trip){
        /* @var $query Doctrine\ORM\Query */
        $query = $this->getEntityManager()->createQuery('SELECT MIN(a.id) '.
                'FROM \System\TrackingBundle\Entity\Position a '.
                'LEFT JOIN \System\TrackingBundle\Entity\Position b '.
                'WITH( b.date_created >= DATETIME_SUB(a.date_created, '.self::TRIPS_INTERVAL.') AND b.date_created < a.date_created AND b.user = :user ) WHERE b.date_created IS NULL AND a.user = :user AND a.id > :start '.
                'GROUP BY a.date_created ORDER BY a.date_created ASC')
                ->setMaxResults(1);
        
        $query
            ->setParameter('start', $trip->getId())
            ->setParameter('user', $user->getId());
        
        return $query->getSingleScalarResult();
    }
}