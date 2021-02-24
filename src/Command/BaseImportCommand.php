<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\BatchEntry;
use App\Entity\BatchEntryPlace;
use App\Repository\BatchEntryPlaceRepository;
use App\Repository\BatchEntryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Serializer;

abstract class BaseImportCommand extends Command
{
    const FLUSH_LIMIT = 5000;
    const CSV_FILE = '';
    const TARGET_VALUE = '';
    const DELIMITER = ';';

    protected EntityManagerInterface $entityManager;
    private BatchEntryRepository $batchEntryRepository;
    private BatchEntryPlaceRepository $batchEntryPlaceRepository;

    /**
     * @var BatchEntryPlace[]
     */
    private array $places = [];

    /**
     * @var BatchEntry[]
     */
    private array $entries = [];

    public function __construct(
        EntityManagerInterface $entityManager,
        BatchEntryRepository $batchEntryRepository,
        BatchEntryPlaceRepository $batchEntryPlaceRepository
    ) {
        $this->entityManager = $entityManager;
        $this->batchEntryRepository = $batchEntryRepository;
        $this->batchEntryPlaceRepository = $batchEntryPlaceRepository;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->write('Reading [' . self::CSV_FILE . ']...');
        $fileContent = file_get_contents(self::CSV_FILE);
        $io->writeln(' done.');

        if (!$fileContent) {
            $io->error('Failed to open file');

            return self::FAILURE;
        }

        $serializer = new Serializer([], [new CsvEncoder([
            CsvEncoder::NO_HEADERS_KEY => true,
            CsvEncoder::DELIMITER_KEY => static::DELIMITER,
        ])]);

        $io->write('Importing...');
        $csvContent = $serializer->decode($fileContent, 'csv');
        $total = count($csvContent);
        $io->writeln($total . ' entries.');

        $i = 0;
        foreach ($csvContent as $csvLine) {
            $companyName = trim($csvLine[0]);
            $place = $this->findOrCreatePlace(trim($csvLine[1]));
            $amount = (int) str_replace(['.', ','], '', $csvLine[2]);

            $this->findOrCreateEntry($companyName, $place, static::TARGET_VALUE, $amount);

            if ($i > 0 && $i % static::FLUSH_LIMIT === 0) {
                $io->writeln("Flushing @ ${i} / ${total}...");
                $this->flush();
            }

            ++$i;
        }

        $io->writeln("Flushing @ ${i} / ${total}");
        $this->flush();

        $io->success('Finished importing ' . static::CSV_FILE);

        return self::SUCCESS;
    }

    protected function flush()
    {
        $this->entityManager->flush();
        $this->entityManager->clear();
        $this->clearEntries();
        gc_collect_cycles();
    }

    protected function clearEntries(): void
    {
        $this->places = [];
        $this->entries = [];
    }

    protected function findOrCreatePlace(string $placeName): BatchEntryPlace
    {
        // First find in local cache
        if (array_key_exists($placeName, $this->places)) {
            return $this->places[$placeName];
        }

        // Then in db
        $existingPlace = $this->batchEntryPlaceRepository->findOneBy([
            'name' => $placeName,
        ]);

        if ($existingPlace != null) {
            $this->places[$placeName] = $existingPlace;

            return $existingPlace;
        }

        // Otherwise persist new and cache
        $newPlace = new BatchEntryPlace($placeName);
        $this->entityManager->persist($newPlace);
        $this->places[$placeName] = $newPlace;

        return $newPlace;
    }

    protected function findOrCreateEntry(string $companyName, BatchEntryPlace $place, $updateKey, int $updateValue): void
    {
        $entryIndex = md5($companyName . $place->getName());
        $callSetter = 'set' . $updateKey;
        // First find in local cache
        if (array_key_exists($entryIndex, $this->entries)) {
            $this->entries[$entryIndex]->$callSetter($updateValue);
            $this->entries[$entryIndex]->recalculateTotalAmount();

            return;
        }

        // Then in db
        $existingEntries = $this->batchEntryRepository->findBy([
            'companyName' => $companyName,
            'place' => $place,
        ]);

        // If not able to match exactly ONE entry with same name and place, create new one
        if (count($existingEntries) !== 1) {
            /* @var $newEntry BatchEntry */
            $newEntry = (new BatchEntry($companyName, $place))->$callSetter($updateValue);
            $newEntry->recalculateTotalAmount();

            $this->entityManager->persist($newEntry);
            $this->entries[$entryIndex] = $newEntry;

            return;
        }

        // Otheriwse, add amount to existing entry
        $existingEntries[0]->$callSetter($updateValue);
        $existingEntries[0]->recalculateTotalAmount();
        $this->entries[$entryIndex] = $existingEntries[0];
    }
}
