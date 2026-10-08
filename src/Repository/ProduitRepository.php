<?php

namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Produit>
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    /**
     * Les X derniers produits ajoutés (les plus récents d'abord).
     */
    public function findDerniers(int $nombre = 3): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.categorie', 'c')
            ->addSelect('c')
            ->orderBy('p.dateAjout', 'DESC')
            ->setMaxResults($nombre)
            ->getQuery()
            ->getResult();
    }

    /**
     * Les X produits marqués "à la une" par l'admin.
     */
    public function findALaUne(int $nombre = 3): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.categorie', 'c')
            ->addSelect('c')
            ->andWhere('p.alaUne = :aLaUne')
            ->setParameter('aLaUne', true)
            ->orderBy('p.dateAjout', 'DESC')
            ->setMaxResults($nombre)
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche des produits par mot-clé et/ou par catégorie.
     * Les deux filtres sont facultatifs.
     */
    public function rechercher(?string $motCle, ?int $categorieId): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.categorie', 'c')
            ->addSelect('c')
            ->leftJoin('p.options', 'o')
            ->addSelect('o')
            ->orderBy('p.dateAjout', 'DESC');

        if ($motCle) {
            $qb->andWhere('p.nom LIKE :motCle OR p.description LIKE :motCle')
               ->setParameter('motCle', '%' . $motCle . '%');
        }

        if ($categorieId) {
            $qb->andWhere('p.categorie = :categorie')
               ->setParameter('categorie', $categorieId);
        }

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Produit[] Returns an array of Produit objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Produit
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
