<?php

declare(strict_types=1);

namespace App\Command\SecondBatch\Version0;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Serializer;

class ConvertTwoZeroCommand extends Command
{
    protected static $defaultName = 'app:convert-2.0';

    private const DEBUG_ENABLED = false;

    private SymfonyStyle $io;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $handle = fopen('./public/file/second-batch/version-0/second-batch-0.txt', 'r');

        $lineNumber = 1;
        $dataArr = [];

        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $lineResult = $this->handleLine($line, $lineNumber);

                if ($lineResult !== null) {
                    $dataArr[] = $lineResult;
                }

                ++$lineNumber;
            }

            fclose($handle);
        } else {
            $this->io->error('Failed to open file');

            return self::FAILURE;
        }

        $serializer = new Serializer([], [new CsvEncoder([
            CsvEncoder::NO_HEADERS_KEY => true,
            CsvEncoder::DELIMITER_KEY => ';',
        ])]);

        (new Filesystem())->dumpFile('./public/file/second-batch/version-0/second-batch-0.csv', $serializer->encode($dataArr, 'csv'));

        $this->io->success('Finished conversion');

        return self::SUCCESS;
    }

    private function handleLine(string $line, int $lineNumber): ?array
    {
        $result = preg_split('/\h{2,}/', trim($line));

        if ((count($result) !== 3) || $result[0] === 'BEDRIJFSNAAM') { // Filter headers/footers and empty lines
            if (
                self::DEBUG_ENABLED
                && count($result) > 0
                && !empty($result[0])
                && $result[0] !== 'BEDRIJFSNAAM'
                && !is_numeric($result[0])
                && $result[0] !== 'VOORSCHOTBEDRAG'
                && $result[0] !== 'Register NOW, tweede aanvraagperiode'
            ) {
                $this->io->warning("$lineNumber: $result");
            }

            return null;
        }

        return $result;
    }
}
