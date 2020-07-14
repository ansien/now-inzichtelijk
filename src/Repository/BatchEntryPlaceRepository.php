<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\BatchEntryPlace;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BatchEntryPlace|null find($id, $lockMode = null, $lockVersion = null)
 * @method BatchEntryPlace|null findOneBy(array $criteria, array $orderBy = null)
 * @method BatchEntryPlace[]    findAll()
 * @method BatchEntryPlace[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BatchEntryPlaceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BatchEntryPlace::class);
    }
}
