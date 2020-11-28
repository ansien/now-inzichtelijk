<?php

declare(strict_types=1);

namespace App\Manager;

use App\Repository\BatchEntryRepository;
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
    private const CACHE_KEY = 'api_batch_entry:';

    private BatchEntryRepository $batchEntryRepository;
    private CacheManager $cacheManager;
    private KernelInterface $kernel;

    public function __construct(
        BatchEntryRepository $batchEntryRepository,
        CacheManager $cacheManager,
        KernelInterface $kernel
    ) {
        $this->batchEntryRepository = $batchEntryRepository;
        $this->cacheManager = $cacheManager;
        $this->kernel = $kernel;
    }

    /**
     * @throws JsonException | InvalidArgumentException
     */
    public function getBatchEntries(int $page, ?string $orderString, ?string $searchString): array
    {
        $page = max($page, 1);

        $cacheKey = base64_encode(self::CACHE_KEY . $page . $orderString . $searchString);
        $cacheClient = $this->cacheManager->getClient();

        $cacheData = null;
        $cacheFailed = false;

        try {
            $cacheData = $cacheClient->get($cacheKey);
        } catch (Exception $e) {
            $cacheFailed = true;
        }

        if ($cacheData === null || $cacheFailed === true) {
            $data = $this->getBatchEntriesFromDatabase($page, $orderString, $searchString);

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

    private function getBatchEntriesFromDatabase(int $page, ?string $orderString, ?string $searchString): array
    {
        $qb = $this->batchEntryRepository->createQueryBuilder('be')
            ->setFirstResult(self::PAGE_SIZE * ($page - 1))
            ->setMaxResults(self::PAGE_SIZE);

        $totalAmountQb = $this->batchEntryRepository->createQueryBuilder('be')
            ->select('SUM(be.totalAmount)');

        if ($searchString) {
            try {
                $searchData = $this->parseQueryString($searchString);
            } catch (Exception $e) {
                return [];
            }

            if (!empty($searchData)) {
                if (array_key_exists('companyName', $searchData)) {
                    $qb->join('be.place', 'p')->andWhere('be.companyName LIKE :companyName')->setParameter('companyName', '%' . strtoupper($searchData['companyName']) . '%');
                    $totalAmountQb->join('be.place', 'p')->andWhere('be.companyName LIKE :companyName')->setParameter('companyName', '%' . strtoupper($searchData['companyName']) . '%');
                }
                if (array_key_exists('placeName', $searchData)) {
                    $qb->join('be.place', 'p')->andWhere('p.name LIKE :placeName')->setParameter('placeName', '%' . strtoupper($searchData['placeName']) . '%');
                    $totalAmountQb->join('be.place', 'p')->andWhere('p.name LIKE :placeName')->setParameter('placeName', '%' . strtoupper($searchData['placeName']) . '%');
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
                    $qb->addOrderBy('be.companyName', strtoupper($orderData['companyName']) === 'ASC' ? 'ASC' : 'DESC');
                    $totalAmountQb->addOrderBy('be.companyName', strtoupper($orderData['companyName']) === 'ASC' ? 'ASC' : 'DESC');
                }
                if (array_key_exists('placeName', $orderData)) {
                    $qb->join('be.place', 'p')->addOrderBy('p.name', strtoupper($orderData['placeName']) === 'ASC' ? 'ASC' : 'DESC');
                    $totalAmountQb->join('be.place', 'p')->addOrderBy('p.name', strtoupper($orderData['placeName']) === 'ASC' ? 'ASC' : 'DESC');
                }
                if (array_key_exists('firstAmount', $orderData)) {
                    $qb->addOrderBy('be.firstAmount', strtoupper($orderData['firstAmount']) === 'ASC' ? 'ASC' : 'DESC');
                    $totalAmountQb->addOrderBy('be.firstAmount', strtoupper($orderData['firstAmount']) === 'ASC' ? 'ASC' : 'DESC');
                }
                if (array_key_exists('secondAmount', $orderData)) {
                    $qb->addOrderBy('be.secondAmount', strtoupper($orderData['secondAmount']) === 'ASC' ? 'ASC' : 'DESC');
                    $totalAmountQb->addOrderBy('be.secondAmount', strtoupper($orderData['secondAmount']) === 'ASC' ? 'ASC' : 'DESC');
                }
                if (array_key_exists('totalAmount', $orderData)) {
                    $qb->addOrderBy('be.totalAmount', strtoupper($orderData['totalAmount']) === 'ASC' ? 'ASC' : 'DESC');
                    $totalAmountQb->addOrderBy('be.totalAmount', strtoupper($orderData['totalAmount']) === 'ASC' ? 'ASC' : 'DESC');
                }
            }
        }

        $paginator = new Paginator($qb->getQuery(), $fetchJoinCollection = false);
        $result = [];

        foreach ($paginator as $l) {
            $result[] = [
                'id' => $l->getId(),
                'companyName' => $l->getCompanyName(),
                'placeName' => $l->getPlace()->getName(),
                'firstAmount' => $l->getFirstAmount(),
                'secondAmount' => $l->getSecondAmount(),
                'totalAmount' => $l->getTotalAmount(),
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
