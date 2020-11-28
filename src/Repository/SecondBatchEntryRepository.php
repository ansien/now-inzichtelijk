<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SecondBatchEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SecondBatchEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method SecondBatchEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method SecondBatchEntry[]    findAll()
 * @method SecondBatchEntry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SecondBatchEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SecondBatchEntry::class);
    }
}
