<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use function Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Sortie>
 *
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function save(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllSorties()
    {
        $queryBuilder = $this->createQueryBuilder('sortie');

        $queryBuilder
            ->leftJoin('sortie.participants', 'participants')->addSelect('participants')
            ->leftJoin('sortie.etat', 'etat')->addSelect('etat')
            ->leftJoin('sortie.organisateur', 'organisateur')->addSelect('organisateur');
        $query = $queryBuilder->getQuery();

        $paginator = new Paginator($query);
        return $paginator;
    }

    public function filterBy($value, $userID)
    {
        $queryBuilder = $this->createQueryBuilder('sortie');
        $queryBuilder->setParameter('campusID', $value->campus->getId());
        $queryBuilder->andWhere('sortie.campus = :campusID');

        if(!empty($value->nom)){
            $queryBuilder->setParameter('searchTerm', '%'."{$value->nom}".'%');
            $queryBuilder->andWhere('sortie.nom LIKE :searchTerm');
        }

        if($value->dateDebut !== null && !empty("{$value->dateDebut->format('d/m/Y')}")){
            $queryBuilder->setParameter('openingDate', $value->dateDebut->format('Y-m-d'). ' 00:00:00');
            if($value->dateFin !== null && !empty("{$value->dateFin->format('d/m/Y')}")){
                $queryBuilder->setParameter('closingDate', $value->dateFin->format('Y-m-d'). ' 23:59:59')
                             ->andWhere($queryBuilder->expr()->between('sortie.dateHeureDebut', ':openingDate', ':closingDate'));
            }
            else{
                $queryBuilder->andWhere($queryBuilder->expr()->gte('sortie.dateHeureDebut', ':openingDate'));
            }
        }elseif($value->dateFin !== null && !empty("{$value->dateFin->format('d/m/Y')}")){
            $queryBuilder->setParameter('closingDate', $value->dateFin->format('Y-m-d'). ' 23:59:59')
                         ->andWhere($queryBuilder->expr()->lte('sortie.dateHeureDebut', ':closingDate'));
        }

        if($value->organisateur !== false){
            $queryBuilder->setParameter('organizer', $userID)
                         ->andWhere('sortie.organisateur = :organizer');
        }

        if($value->inscrit !== false && $value->inscrit !==null ){
            $queryBuilder->setParameter('userID', $userID)
                         ->innerJoin('sortie.participants', 'p','WITH', 'p.id = :userID');
        }

        if($value->nonInscrit !== false){
            $queryBuilder->setParameter('usrID', $userID)
                         ->leftJoin('sortie.participants', 'pa','WITH', 'pa.id != :usrID')->addSelect('pa');
        }else{
            $queryBuilder->leftJoin('sortie.participants', 'participants')->addSelect('participants');
        }

        if($value->sortiesPassees !== false){
            $endDate = new \DateTime('now');
            $queryBuilder->setParameter('closingDate', $endDate)
                         ->andWhere($queryBuilder->expr()->lte('sortie.dateHeureDebut', ':closingDate'));
        }



        $query = $queryBuilder->getQuery();
        $paginator = new Paginator($query);
        return $paginator;
    }

//    /**
//     * @return Sortie[] Returns an array of Sortie objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Sortie
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

}
