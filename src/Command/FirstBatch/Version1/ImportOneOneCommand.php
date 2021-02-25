<?php

declare(strict_types=1);

namespace App\Command\FirstBatch\Version1;

use App\Command\BaseImportCommand;
use App\Repository\BatchEntryPlaceRepository;
use App\Repository\BatchEntryRepository;
use Doctrine\ORM\EntityManagerInterface;

class ImportOneOneCommand extends BaseImportCommand
{
    protected static $defaultName = 'app:import-1.1';

    const CSV_FILE = './public/file/first-batch/version-1/first-batch-1.csv';
    const TARGET_VALUE = 'OneOneAmount';

    public function __construct(
        EntityManagerInterface $entityManager,
        BatchEntryRepository $batchEntryRepository,
        BatchEntryPlaceRepository $batchEntryPlaceRepository
    ) {
        parent::__construct($entityManager, $batchEntryRepository, $batchEntryPlaceRepository);
    }
}
