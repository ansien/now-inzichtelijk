<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\FirstBatchEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FirstBatchEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method FirstBatchEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method FirstBatchEntry[]    findAll()
 * @method FirstBatchEntry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FirstBatchEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FirstBatchEntry::class);
    }
}
