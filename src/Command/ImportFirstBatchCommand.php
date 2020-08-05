<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\BatchEntryPlace;
use App\Entity\FirstBatchEntry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Serializer;

class ImportFirstBatchCommand extends Command
{
    protected static $defaultName = 'app:import-first-batch';

    private EntityManagerInterface $entityManager;
    private array $createdPlaces = [];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Imports the first batch of NOW data');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $fileContent = file_get_contents('./public/file/first-batch.csv');

        if (!$fileContent) {
            $io->success('Failed to open file');

            return self::FAILURE;
        }

        $serializer = new Serializer([], [new CsvEncoder([
            CsvEncoder::NO_HEADERS_KEY => true,
            CsvEncoder::DELIMITER_KEY => ';',
        ])]);

        $csvContent = $serializer->decode($fileContent, 'csv');

        $i = 0;
        foreach ($csvContent as $csvLine) {
            ++$i;

            if ($i <= 1) { // Skip header
                continue;
            }

            $place = $this->findOrCreatePlace(trim($csvLine[2]));

            $registryLine = new FirstBatchEntry($csvLine[1], $place, (int) $csvLine[3], (int) $csvLine[4]);
            $this->entityManager->persist($registryLine);

            if ($i % 10000 === 0) {
                $this->entityManager->flush();
                $io->writeln("Flushing @ ${i}");
            }
        }

        $this->entityManager->flush();

        $io->success('Finished importing batch one');

        return self::SUCCESS;
    }

    private function findOrCreatePlace(string $placeName): BatchEntryPlace
    {
        if (array_key_exists($placeName, $this->createdPlaces)) {
            return $this->createdPlaces[$placeName];
        }

        $place = new BatchEntryPlace($placeName);
        $this->entityManager->persist($place);
        $this->createdPlaces[$placeName] = $place;

        return $place;
    }
}
