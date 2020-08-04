<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\FirstBatchEntryRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Serializer;

class CustomCsvExportCommand extends Command
{
    protected static $defaultName = 'app:detailed-export';

    private FirstBatchEntryRepository $entryRepository;

    public function __construct(FirstBatchEntryRepository $entryRepository)
    {
        $this->entryRepository = $entryRepository;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Creates a more detailed CSV export');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $entries = $this->entryRepository->findAll();

        $dataArr = [
            ['Id', 'Bedrijfsnaam', 'Vestigingsplaats', 'Bedrag', 'Lat', 'Long', 'Land code', 'Land naam', 'Admin niveau 1 (naam)', 'Admin niveau 1 (code)', 'Admin niveau 2 (naam)', 'Admin niveau 2 (code)', 'Admin niveau 3 (naam)', 'Admin niveau 3 (code)', 'Admin niveau 4 (naam)', 'Admin niveau 4 (code)'],
        ];

        foreach ($entries as $entry) {
            $tempArr = [
                $entry->getId(),
                $entry->getCompanyName(),
                $entry->getPlace()->getName(),
                $entry->getAmount(),
                $entry->getPlace()->getLatitude(),
                $entry->getPlace()->getLongitude(),
                $entry->getPlace()->getCountry() ? $entry->getPlace()->getCountry()->getCode() : null,
                $entry->getPlace()->getCountry() ? $entry->getPlace()->getCountry()->getName() : null,
            ];

            foreach ($entry->getPlace()->getAdminLevels() as $adminLevel) {
                $tempArr[] = [
                    $adminLevel->getName(),
                    $adminLevel->getCode(),
                ];
            }

            $dataArr[] = $tempArr;
        }

        $serializer = new Serializer([], [new CsvEncoder([
            CsvEncoder::NO_HEADERS_KEY => true,
            CsvEncoder::DELIMITER_KEY => ';',
        ])]);

        file_put_contents(
            './public/file/first-batch-detailed.csv',
            $serializer->encode($dataArr, 'csv')
        );

        $io->success('Finished exporting');

        return 0;
    }
}
