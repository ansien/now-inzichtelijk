<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\ApiRequest;
use Doctrine\ORM\EntityManagerInterface;

class ApiRequestManager
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function saveApiRequest(string $endpoint, array $query): void
    {
        $searchQuery = new ApiRequest($endpoint, $query);

        $this->entityManager->persist($searchQuery);
        $this->entityManager->flush();
    }
}
