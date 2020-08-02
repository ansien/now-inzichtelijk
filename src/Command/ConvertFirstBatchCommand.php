<?php

declare(strict_types=1);

namespace App\Command;

use Exception;
use Smalot\PdfParser\Parser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Serializer;

class ConvertFirstBatchCommand extends Command
{
    protected static $defaultName = 'app:convert-first-batch';

    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Converts the first batch of NOW data from PDF to CSV');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $parser = new Parser();

        try {
            $pdf = $parser->parseFile('./public/file/first-batch.pdf');
            $pages = $pdf->getPages();
        } catch (Exception $e) {
            $io->error('Failed to parse PDF');

            return 1;
        }

        $dataArr = [['Id', 'Bedrijfsnaam', 'Vestigingsplaats', 'Bedrag', 'Pagina']];
        $ignoredWords = ['BEDRIJFSNAAM', 'VESTIGINGSPLAATS', 'UITBETAALD', 'VOORSCHOTBEDRAG'];
        $doubleLinePlaces = ['ALBRANDSWAARD', 'SMALLINGERLND', 'WESTERKWARTIER', 'STUKENBROCK', 'PD'];

        $id = 0;

        for ($page = 2; $page < 2046; ++$page) {
            $pageData = $pages[$page];

            if (count($pageData->getTextArray()) <= 0) {
                $io->writeln("Failed at page: $page");
            }

            $textArr = $pageData->getTextArray();
            array_pop($textArr); // Remove page number

            $tempArr = [];

            foreach ($textArr as $t) {
                if (in_array(trim($t), $ignoredWords)) { // Ignore headers
                    continue;
                }

                $tempArr[] = $t;

                // Fix for text on multiple lines
                if (is_numeric(str_replace(['.', ','], '', $t)) === false && count($tempArr) >= 3) {
                    $newArr = [];

                    if (in_array($t, $doubleLinePlaces, true) === true) {
                        $newArr[0] = $tempArr[0];
                        $newArr[1] = $tempArr[1] . $tempArr[2];
                    } else {
                        $newArr[0] = $tempArr[0] . $tempArr[1];
                        $newArr[1] = $tempArr[2];
                    }

                    $tempArr = $newArr;
                }

                if (count($tempArr) >= 3) {
                    array_unshift($tempArr, $id); // Prepend ID
                    $tempArr[] = $page + 1; // Append page number
                    $tempArr[3] = str_replace(['.', ','], '', $tempArr[3]); // Clean amount
                    $dataArr[] = $tempArr; // Add to total
                    $tempArr = []; // Clear temp array
                    ++$id;
                }
            }
        }

        $serializer = new Serializer([], [new CsvEncoder([
            CsvEncoder::NO_HEADERS_KEY => true,
            CsvEncoder::DELIMITER_KEY => ';',
        ])]);

        file_put_contents(
            './public/file/first-batch.csv',
            $serializer->encode($dataArr, 'csv')
        );

        $io->success('Finished converting batch one');

        return 0;
    }
}
