<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\FirstBatchEntry;
use App\Entity\SecondBatchEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Exception;
use InvalidArgumentException;
use JsonException;
use Symfony\Component\HttpKernel\KernelInterface;

class BatchEntryApiManager
{
    private const PAGE_SIZE = 15;
    private const FIRST_BATCH_CACHE_KEY = 'api_batch_entry:';

    private EntityManagerInterface $entityManager;
    private CacheManager $cacheManager;
    private KernelInterface $kernel;

    public function __construct(
        EntityManagerInterface $entityManager,
        CacheManager $cacheManager,
        KernelInterface $kernel
    ) {
        $this->entityManager = $entityManager;
        $this->cacheManager = $cacheManager;
        $this->kernel = $kernel;
    }

    /**
     * @throws JsonException | InvalidArgumentException
     */
    public function getBatchEntries(int $batch, int $page, ?string $orderString, ?string $searchString): array
    {
        $page = max($page, 1);

        $cacheKey = base64_encode(self::FIRST_BATCH_CACHE_KEY . $batch . $page . $orderString . $searchString);
        $cacheClient = $this->cacheManager->getClient();

        $cacheData = null;
        $cacheFailed = false;

        try {
            $cacheData = $cacheClient->get($cacheKey);
        } catch (Exception $e) {
            $cacheFailed = true;
        }

        if ($batch === 1) {
            $repository = $this->entityManager->getRepository(FirstBatchEntry::class);
        } elseif ($batch === 2) {
            $repository = $this->entityManager->getRepository(SecondBatchEntry::class);
        } else {
            throw new InvalidArgumentException('Unsupported batch number provided');
        }

        if ($cacheData === null || $cacheFailed === true) {
            $data = $this->getBatchEntriesFromDatabase($repository, $page, $orderString, $searchString);

            if ($cacheFailed === false) {
                $encodedData = json_encode($data, JSON_THROW_ON_ERROR);
                $cacheClient->set($cacheKey, $encodedData);
            }
        } else {
            $data = json_decode($cacheData, true, 512, JSON_THROW_ON_ERROR);

            if (!is_array($data)) {
                return [];
            }
        }

        if (strtoupper($this->kernel->getEnvironment()) !== 'PROD') {
            $data['cacheHit'] = !($cacheData === null);
        }

        return $data;
    }

    private function getBatchEntriesFromDatabase(ServiceEntityRepository $repository, int $page, ?string $orderString, ?string $searchString): array
    {
        $qb = $repository->createQueryBuilder('rl')
            ->addSelect('rl.id, rl.companyName, p.name as placeName, rl.amount')
            ->leftJoin('rl.place', 'p')
            ->setFirstResult(self::PAGE_SIZE * ($page - 1))
            ->setMaxResults(self::PAGE_SIZE);

        $totalAmountQb = $repository->createQueryBuilder('rl')
            ->select('SUM(rl.amount)')
            ->leftJoin('rl.place', 'p');

        if ($searchString) {
            try {
                $searchData = $this->parseQueryString($searchString);
            } catch (Exception $e) {
                return [];
            }

            if (!empty($searchData)) {
                if (array_key_exists('companyName', $searchData)) {
                    $qb->andWhere('rl.companyName LIKE :companyName')->setParameter('companyName', '%' . strtoupper($searchData['companyName']) . '%');
                    $totalAmountQb->andWhere('rl.companyName LIKE :companyName')->setParameter('companyName', '%' . strtoupper($searchData['companyName']) . '%');
                }
                if (array_key_exists('placeName', $searchData)) {
                    $qb->andWhere('p.name LIKE :placeName')->setParameter('placeName', '%' . strtoupper($searchData['placeName']) . '%');
                    $totalAmountQb->andWhere('p.name LIKE :placeName')->setParameter('placeName', '%' . strtoupper($searchData['placeName']) . '%');
                }
            }
        }

        if ($orderString) {
            try {
                $orderData = $this->parseQueryString($orderString);
            } catch (Exception $e) {
                return [];
            }

            if (!empty($orderData)) {
                if (array_key_exists('companyName', $orderData)) {
                    $qb->addOrderBy('rl.companyName', strtoupper($orderData['companyName']) === 'ASC' ? 'ASC' : 'DESC');
                    $totalAmountQb->addOrderBy('rl.companyName', strtoupper($orderData['companyName']) === 'ASC' ? 'ASC' : 'DESC');
                }
                if (array_key_exists('placeName', $orderData)) {
                    $qb->addOrderBy('p.name', strtoupper($orderData['placeName']) === 'ASC' ? 'ASC' : 'DESC');
                    $totalAmountQb->addOrderBy('p.name', strtoupper($orderData['placeName']) === 'ASC' ? 'ASC' : 'DESC');
                }
                if (array_key_exists('amount', $orderData)) {
                    $qb->addOrderBy('rl.amount', strtoupper($orderData['amount']) === 'ASC' ? 'ASC' : 'DESC');
                    $totalAmountQb->addOrderBy('rl.amount', strtoupper($orderData['amount']) === 'ASC' ? 'ASC' : 'DESC');
                }
            }
        }

        $paginator = new Paginator($qb->getQuery(), $fetchJoinCollection = false);
        $result = [];

        foreach ($paginator as $l) {
            $result[] = [
                'id' => $l['id'],
                'companyName' => $l['companyName'],
                'placeName' => $l['placeName'],
                'amount' => $l['amount'],
            ];
        }

        $totalAmount = 0;

        try {
            $totalAmount = (int) $totalAmountQb->getQuery()->getSingleScalarResult();
        } catch (NoResultException | NonUniqueResultException $e) {
        }

        return [
            'result' => $result,
            'totalAmount' => $totalAmount,
            'totalResults' => $paginator->count(),
            'page' => $page,
        ];
    }

    private function parseQueryString(string $queryString): array
    {
        $data = [];
        $keys = explode(',', $queryString);

        foreach ($keys as $k) {
            $d = explode(':', $k);
            $data[$d[0]] = $d[1];
        }

        return $data;
    }
}
