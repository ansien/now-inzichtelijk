<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use App\Repository\EntryRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Exception;
use InvalidArgumentException;
use JsonException;
use Symfony\Component\HttpKernel\KernelInterface;

final class BatchEntryApiManager
{
    private const PAGE_SIZE = 15;

    private const CACHE_KEY = 'api_batch_entry:';

    private const ALLOWED_ORDER_KEYS = [
        'companyName' => 'c.companyName',
        'placeName' => 'c.placeName',
        'depositedAmount' => 'depositedAmountSum',
        'updatedAmount' => 'updatedAmountSum',
    ];

    private CacheManager $cacheManager;

    private KernelInterface $kernel;

    private EntryRepository $entryRepository;

    private CompanyRepository $companyRepository;

    public function __construct(
        CacheManager $cacheManager,
        KernelInterface $kernel,
        EntryRepository $entryRepository,
        CompanyRepository $companyRepository
    ) {
        $this->cacheManager = $cacheManager;
        $this->kernel = $kernel;
        $this->entryRepository = $entryRepository;
        $this->companyRepository = $companyRepository;
    }

    /**
     * @throws JsonException|InvalidArgumentException
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
        } catch (Exception) {
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
        $qb = $this->companyRepository->createQueryBuilder('c')
            ->select('c as company, SUM(e.depositedAmount) as depositedAmountSum, SUM(e.updatedAmount) as updatedAmountSum')
            ->join('c.entries', 'e')
            ->groupBy('c.id')
            ->setFirstResult(self::PAGE_SIZE * ($page - 1))
            ->setMaxResults(self::PAGE_SIZE);

        $totalAmountQb = $this->entryRepository->createQueryBuilder('e')
            ->select('SUM(e.depositedAmount) as depositedAmountSum, SUM(e.updatedAmount) as updatedAmountSum')
            ->join('e.company', 'c');

        if ($searchString) {
            try {
                $searchData = $this->parseQueryString($searchString);
            } catch (Exception) {
                return [];
            }

            if (!empty($searchData)) {
                if (array_key_exists('companyName', $searchData)) {
                    $qb->andWhere('c.companyName LIKE :companyName')->setParameter('companyName', '%' . strtoupper($searchData['companyName']) . '%');
                    $totalAmountQb->andWhere('c.companyName LIKE :companyName')->setParameter('companyName', '%' . strtoupper($searchData['companyName']) . '%');
                }
                if (array_key_exists('placeName', $searchData)) {
                    $qb->andWhere('c.placeName LIKE :placeName')->setParameter('placeName', '%' . strtoupper($searchData['placeName']) . '%');
                    $totalAmountQb->andWhere('c.placeName LIKE :placeName')->setParameter('placeName', '%' . strtoupper($searchData['placeName']) . '%');
                }
            }
        }

        if ($orderString) {
            try {
                $orderData = $this->parseQueryString($orderString);
            } catch (Exception) {
                return [];
            }

            foreach ($orderData as $orderKey => $orderDirection) {
                if (!array_key_exists($orderKey, self::ALLOWED_ORDER_KEYS)) {
                    continue;
                }

                $qb->addOrderBy(self::ALLOWED_ORDER_KEYS[$orderKey], strtoupper($orderDirection) === 'ASC' ? 'ASC' : 'DESC');
                $totalAmountQb->addOrderBy(self::ALLOWED_ORDER_KEYS[$orderKey], strtoupper($orderDirection) === 'ASC' ? 'ASC' : 'DESC');
            }
        }

        $paginator = new Paginator($qb->getQuery(), false);
        $result = [];

        foreach ($paginator as $e) {
            $result[] = [
                'companyId' => $e['company']->getId(),
                'companyName' => $e['company']->getCompanyName(),
                'placeName' => !empty($e['company']->getPlaceName()) ? $e['company']->getPlaceName() : '-',
                'depositedAmount' => $e['depositedAmountSum'],
                'updatedAmount' => $e['updatedAmountSum'],
            ];
        }

        $totalDepositedAmount = 0;
        try {
            $totalDepositedAmount = (int) $totalAmountQb->getQuery()->getScalarResult()[0]['depositedAmountSum'];
        } catch (NoResultException|NonUniqueResultException) {
        }

        return [
            'result' => $result,
            'totalAmount' => $totalDepositedAmount,
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

    public function getEntriesForCompany(Company $company): array
    {
        $result = [];

        foreach ($company->getEntries() as $entry) {
            $result[] = [
                'batch' => $entry->getBatch(),
                'depositedAmount' => $entry->getDepositedAmount(),
                'updatedAmount' => $entry->getUpdatedAmount(),
            ];
        }

        return $result;
    }
}
