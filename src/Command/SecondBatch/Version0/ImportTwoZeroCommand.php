<?php

declare(strict_types=1);

namespace App\Command\SecondBatch\Version0;

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

class ImportTwoZeroCommand extends Command
{
    protected static $defaultName = 'app:import-2.0';

    private EntityManagerInterface $entityManager;
    private BatchEntryRepository $batchEntryRepository;
    private BatchEntryPlaceRepository $batchEntryPlaceRepository;

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

    protected function configure(): void
    {
        $this->setDescription('Imports the second batch of NOW data');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $fileContent = file_get_contents('./public/file/second-batch/version-0/second-batch-0.csv');

        if (!$fileContent) {
            $io->success('Failed to open file');

            return self::FAILURE;
        }

        $serializer = new Serializer([], [new CsvEncoder([
            CsvEncoder::NO_HEADERS_KEY => true,
            CsvEncoder::DELIMITER_KEY => ';',
        ])]);

        $csvContent = $serializer->decode($fileContent, 'csv');

        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);

        $i = 0;
        foreach ($csvContent as $csvLine) {
            $this->findOrCreateEntry($csvLine);

            if ($i > 0 && $i % 5000 === 0) {
                $this->entityManager->flush();
                $io->writeln("Flushing @ ${i}");
            }

            ++$i;
        }

        $this->entityManager->flush();

        $io->success('Finished importing');

        return self::SUCCESS;
    }

    private function findOrCreatePlace(string $placeName): BatchEntryPlace
    {
        $existingPlace = $this->batchEntryPlaceRepository->findOneBy([
            'name' => $placeName,
        ]);

        if ($existingPlace) {
            return $existingPlace;
        }

        $place = new BatchEntryPlace($placeName);
        $this->entityManager->persist($place);
        $this->entityManager->flush();

        return $place;
    }

    private function findOrCreateEntry(array $csvLine): void
    {
        $place = $this->findOrCreatePlace(trim($csvLine[1]));
        $amount = str_replace(['.', ','], '', $csvLine[2]);

        $existingEntries = $this->batchEntryRepository->findBy([
            'companyName' => trim($csvLine[0]),
            'place' => $place,
        ]);

        if (count($existingEntries) === 1) { // If able to match on name and place, add amount to existing entry, otherwise; create new one
            $existingEntries[0]
                ->setTwoZeroAmount((int) $amount)
                ->recalculateTotalAmount();
        } else {
            $entry = (new BatchEntry(trim($csvLine[0]), $place))
                ->setTwoZeroAmount((int) $amount)
                ->recalculateTotalAmount();

            $this->entityManager->persist($entry);
        }
    }
}
