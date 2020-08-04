<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\BatchEntryAdminLevel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BatchEntryAdminLevel|null find($id, $lockMode = null, $lockVersion = null)
 * @method BatchEntryAdminLevel|null findOneBy(array $criteria, array $orderBy = null)
 * @method BatchEntryAdminLevel[]    findAll()
 * @method BatchEntryAdminLevel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BatchEntryAdminLevelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BatchEntryAdminLevel::class);
    }
}
