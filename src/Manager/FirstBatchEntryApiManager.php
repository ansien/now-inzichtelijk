<?php

declare(strict_types=1);

namespace App\Manager;

use App\Repository\FirstBatchEntryRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Exception;
use JsonException;
use Symfony\Component\HttpKernel\KernelInterface;

class FirstBatchEntryApiManager
{
    private const PAGE_SIZE = 15;
    private const FIRST_BATCH_CACHE_KEY = 'api_first_batch:';

    private FirstBatchEntryRepository $firstBatchEntryRepository;
    private CacheManager $cacheManager;
    private KernelInterface $kernel;

    public function __construct(
        FirstBatchEntryRepository $firstBatchEntryRepository,
        CacheManager $cacheManager,
        KernelInterface $kernel
    ) {
        $this->firstBatchEntryRepository = $firstBatchEntryRepository;
        $this->cacheManager = $cacheManager;
        $this->kernel = $kernel;
    }

    /**
     * @throws JsonException
     */
    public function getFirstBatchEntries(int $page, ?string $orderString, ?string $searchString): array
    {
        $cacheKey = base64_encode(self::FIRST_BATCH_CACHE_KEY . $page . $orderString . $searchString);
        $cacheClient = $this->cacheManager->getClient();

        $cacheData = null;
        $cacheFailed = false;

        try {
            $cacheData = $cacheClient->get($cacheKey);
        } catch (Exception $e) {
            $cacheFailed = true;
        }

        if ($cacheData === null || $cacheFailed === true) {
            $data = $this->getFirstBatchEntriesFromDatabase($page, $orderString, $searchString);

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

        if ($this->kernel->getEnvironment() !== 'prod') {
            $data['cacheHit'] = !($cacheData === null);
        }

        return $data;
    }

    private function getFirstBatchEntriesFromDatabase(int $page, ?string $orderString, ?string $searchString): array
    {
        $qb = $this->firstBatchEntryRepository->createQueryBuilder('rl')
            ->addSelect('rl.id, rl.companyName, p.name as placeName, rl.amount')
            ->leftJoin('rl.place', 'p')
            ->setFirstResult(self::PAGE_SIZE * ($page - 1))
            ->setMaxResults(self::PAGE_SIZE);

        if ($searchString) {
            try {
                $searchData = $this->parseQueryString($searchString);
            } catch (Exception $e) {
                return [];
            }

            if (!empty($searchData)) {
                if (array_key_exists('companyName', $searchData)) {
                    $qb->andWhere('rl.companyName LIKE :companyName')->setParameter('companyName', '%' . strtoupper($searchData['companyName']) . '%');
                }
                if (array_key_exists('placeName', $searchData)) {
                    $qb->andWhere('p.name LIKE :placeName')->setParameter('placeName', '%' . strtoupper($searchData['placeName']) . '%');
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
                }
                if (array_key_exists('placeName', $orderData)) {
                    $qb->addOrderBy('p.name', strtoupper($orderData['placeName']) === 'ASC' ? 'ASC' : 'DESC');
                }
                if (array_key_exists('amount', $orderData)) {
                    $qb->addOrderBy('rl.amount', strtoupper($orderData['amount']) === 'ASC' ? 'ASC' : 'DESC');
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

        return [
            'result' => $result,
            'total' => $paginator->count(),
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
