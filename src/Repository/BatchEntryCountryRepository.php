<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\BatchEntryCountry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BatchEntryCountry|null find($id, $lockMode = null, $lockVersion = null)
 * @method BatchEntryCountry|null findOneBy(array $criteria, array $orderBy = null)
 * @method BatchEntryCountry[]    findAll()
 * @method BatchEntryCountry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BatchEntryCountryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BatchEntryCountry::class);
    }
}
