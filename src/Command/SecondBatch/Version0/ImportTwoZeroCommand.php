<?php

declare(strict_types=1);

namespace App\Command\SecondBatch\Version0;

use App\Command\BaseImportCommand;
use App\Repository\BatchEntryPlaceRepository;
use App\Repository\BatchEntryRepository;
use Doctrine\ORM\EntityManagerInterface;

class ImportTwoZeroCommand extends BaseImportCommand
{
    protected static $defaultName = 'app:import-2.0';

    const CSV_FILE = './public/file/second-batch/version-0/second-batch-0.csv';
    const TARGET_VALUE = 'TwoZeroAmount';

    public function __construct(
        EntityManagerInterface $entityManager,
        BatchEntryRepository $batchEntryRepository,
        BatchEntryPlaceRepository $batchEntryPlaceRepository
    ) {
        parent::__construct($entityManager, $batchEntryRepository, $batchEntryPlaceRepository);
    }
}
