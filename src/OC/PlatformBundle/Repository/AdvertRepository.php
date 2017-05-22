<?php

namespace OC\PlatformBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * AdvertRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AdvertRepository extends \Doctrine\ORM\EntityRepository
{
    public function myFindAll()
    {
        // Méthode 1: en passant par l'Entity Manager
        $querybuilder = $this->_em->createQueryBuilder()
                                  ->select('a')
                                  ->from($this->_entityName, 'a');

        /* Dans un repository, $this->_entityName est le namespace de l'entité gérée
           Ici, il vaut donc OC\PlatformBundle\Entity\Advert
        */

        // Méthode 2: en passant par le raccourci (recommandé)
        $queryBuilder2 = $this->createQueryBuilder('a');

        /* On n'ajoute pas de critère ou de tri particulier, la construction
           de notre requête est finie. 
        */

        // On récupère la Query à partir du QueryBuilder
        $query = $queryBuilder2->getQuery();

        // On récupère les résultats à partir de la Query
        $results = $query->getResult();

        // On retourne les résultats
        return $results;

        /* Version raccourcie

            $this->createQueryBuilder('a')
                 ->getQuery()
                 ->getResult();
        */
    }

    public function myFindOne($id)
    {
        $querybuilder = $this->createQueryBuilder('a');

        $querybuilder->where('a.id = :id')->setParameter('id', $id);

        return $querybuilder->getQuery()->getResult();
    }

    public function findByAuthorAndDate($author, $date)
    {
        $querybuilder = $this->createQueryBuilder('a');

        $querybuilder->where('a.author = :author')
                     ->setParameter('author', $author)
                     ->andWhere('a.date < :year')
                     ->setParameter('year', $year)
                     ->orderBy('a.date', 'DESC');

        return $querybuilder->getQuery()->getResult();
    }

    public function whereCurrentYear(QueryBuilder $qb)
    {
        $qb->andWhere('a.date BETWEEN :start AND :end')
           ->setParameter('start', new \Datetime(date('Y').'-01-01'))
           ->setParameter('end', new \Datetime(date('Y').'-12-31'));
        
        return $qb->getQuery()->getResult();
    }

    public function getAdvertWithApplications()
    {
        /*******************************************************************************************************************************
         Création d'une jointure avec la méthode leftJoin() (ou  innerJoin() pour faire l'équivalent d'un "INNER JOIN").
         Premier argument: l'attribut de l'entité principale (celle qui est dans le "FROM" de la requête) sur lequel faire la jointure. 
                           Dans l'exemple, l'entité "Advert" possède un attribut applications.
         Deuxième argument: L'alias de l'entité jointe (choisi de façon arbitraire).

         Puis on sélectionne également l'entité jointe, via un "addSelect()".
         En effet, un "select('app')" tout court aurait écrasé le "select('a')" déjà fait par le "createQueryBuilder()".
         *******************************************************************************************************************************/

        $qb = $this->createQueryBuilder('a')
                   ->leftJoin('a.applications', 'app')
                   ->addSelect('app');
        
        return $qb->getQuery()->getResult();
    }

    public function getAdvertWithCategories(array $categoryNames)
    {
        $qb = $this->createQueryBuilder('a');

        // Jointure avec "Category"
        // Dans l'entité "Advert" $private $categories
        $qb->innerJoin('a.categories', 'c')
           ->addSelect('c');
        
        // On filtre le nom des catégories à l'aide d'un "IN"
        $qb->where($qb->expr()->in('c.name', $categoryNames));

        return $qb->getQuery()->getResult();
    }

    public function getAdverts($page, $nbPerPage)
    {        
        $query = $this->createQueryBuilder('a')
                      # Jointure sur l'attribut image
                      ->leftJoin('a.image', 'i')
                      ->addSelect('i')
                      # Jointure sur l'attribut catégorie
                      ->leftJoin('a.categories', 'c')
                      ->addSelect('c')
                      ->orderBy('a.date', 'DESC')
                      ->getQuery();
        
        $query
              # Définition de la première annonce à partir de laquelle commencer la liste
              ->setFirstResult( ($page-1) * $nbPerPage )
              # Nombre d'annonce à afficher par page
              ->setMaxResults($nbPerPage);
        
        # On retourne le resultat
        return new Paginator($query, true);
    }
}
