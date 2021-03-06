<?php

namespace OC\PlatformBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ApplicationRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ApplicationRepository extends \Doctrine\ORM\EntityRepository
{
    public function getApplicationsWithAdvert($limit)
    {
        $qb = $this->createQueryBuilder('a');

        // Jointure avec "Advert" (avec pour alias "adv")
        $qb->innerJoin('a.advert', 'adv')
           ->addSelect('adv');

        // On ne retourne que $limit résultats
        $qb->setMaxResults($limit);

        // On retourne le résultat
        return $qb->getQuery()->getResult();
    }
}
