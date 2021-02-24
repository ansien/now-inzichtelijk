<?php

declare(strict_types=1);

namespace App\Command\ThirdBatch\Version0;

use App\Command\BaseImportCommand;
use App\Repository\BatchEntryPlaceRepository;
use App\Repository\BatchEntryRepository;
use Doctrine\ORM\EntityManagerInterface;

class ImportThreeZeroCommand extends BaseImportCommand
{
    protected static $defaultName = 'app:import-3.0';

    const CSV_FILE = './public/file/third-batch/version-0/third-batch-0.csv';
    const TARGET_VALUE = 'ThreeZeroAmount';
    const DELIMITER = ',';

    public function __construct(
        EntityManagerInterface $entityManager,
        BatchEntryRepository $batchEntryRepository,
        BatchEntryPlaceRepository $batchEntryPlaceRepository
    ) {
        parent::__construct($entityManager, $batchEntryRepository, $batchEntryPlaceRepository);
    }
}
