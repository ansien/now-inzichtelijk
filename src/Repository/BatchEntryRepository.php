<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\BatchEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BatchEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method BatchEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method BatchEntry[]    findAll()
 * @method BatchEntry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BatchEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BatchEntry::class);
    }
}
