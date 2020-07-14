<?php

declare(strict_types=1);

namespace App\Command;

use Exception;
use Smalot\PdfParser\Parser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
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
        $helper = $this->getHelper('question');

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

        $pageNumber = 0;
        $id = 0;

        foreach ($pages as $page) {
            ++$pageNumber;

            if ($pageNumber <= 2 || $pageNumber >= 2047) {
                continue;
            }

            if (count($page->getTextArray()) <= 0) {
                $io->writeln("Failed at page: $pageNumber");
            }

            $textArr = $page->getTextArray();
            array_pop($textArr); // Remove page number

            $tempArr = [];

            foreach ($textArr as $t) {
                if (in_array(trim($t), $ignoredWords)) { // Ignore headers
                    continue;
                }

                $tempArr[] = $t;

                // "Manual" fix for data on multiple lines
                if (count($tempArr) >= 3 && !preg_match('/^d+$/', $t)) {
                    $implodedTempArr = implode(';', $tempArr);
                    $question = new ChoiceQuestion("$implodedTempArr - Merge companyName or placeName?", ['companyName', 'placeName']);
                    $response = $helper->ask($input, $output, $question);

                    $newArr = [];

                    if ($response === 'companyName') {
                        $newArr[0] = $tempArr[0] . $tempArr[1];
                        $newArr[1] = $tempArr[2];
                    } else {
                        $newArr[0] = $tempArr[0];
                        $newArr[1] = $tempArr[1] . $tempArr[2];
                    }

                    $tempArr = $newArr;
                }

                if (count($tempArr) >= 3) {
                    array_unshift($tempArr, $id); // Prepend ID
                    $tempArr[] = $pageNumber; // Append page number
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
            './public/file/batch-one.csv',
            $serializer->encode($dataArr, 'csv')
        );

        $io->success('Finished converting batch one');

        return 0;
    }
}
