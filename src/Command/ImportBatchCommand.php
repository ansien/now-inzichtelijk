<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Company;
use App\Entity\Entry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'app:import-batch',
)]
final class ImportBatchCommand extends Command
{
    private const FINALIZED_BATCHES = [1, 2];

    private const DELIMITER = ';';

    private const FLUSH_LIMIT = 5000;

    private array $companyCache = [];

    private EntityManagerInterface $entityManager;

    private KernelInterface $kernel;

    public function __construct(EntityManagerInterface $entityManager, KernelInterface $kernel, string $name = null)
    {
        parent::__construct($name);

        $this->entityManager = $entityManager;
        $this->kernel = $kernel;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('batchNumber', InputArgument::REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $batchNumber = (int) $input->getArgument('batchNumber');

        $isFinal = false;
        if (in_array($batchNumber, self::FINALIZED_BATCHES, true)) {
            $isFinal = true;
        }

        if ($isFinal === true) {
            $filename = "batch-$batchNumber-final.csv";
        } else {
            $filename = "batch-$batchNumber.csv";
        }

        $file = fopen($this->kernel->getProjectDir() . '/public/file/' . $filename, 'r');

        if (!$file) {
            $io->error("Failed to load batch file $filename.");

            return Command::FAILURE;
        }

        $i = 0;
        while (($line = fgets($file)) !== false) {
            ++$i;

            $content = explode(self::DELIMITER, $line);

            if ($i <= 1) {
                continue;
            }

            if ($isFinal === false && (count($content) !== 3)) {
                continue;
            }

            if ($isFinal === true && (count($content) !== 4)) {
                continue;
            }

            $companyName = $this->cleanInput($content[0]);
            $placeName = $this->cleanInput($content[1]);
            $company = $this->findOrCreateCompany($companyName, $placeName);

            $depositedAmount = $this->cleanInput($content[2]);
            $depositedAmountInt = (int) str_replace(['.', ','], '', $depositedAmount);

            $updatedAmountInt = null;
            if ($isFinal === true) {
                $updatedAmount = $this->cleanInput($content[3]);
                $updatedAmountInt = (int) str_replace(['.', ','], '', $updatedAmount);
            }

            $this->createEntry($batchNumber, $company, $depositedAmountInt, $updatedAmountInt);

            if ($i % self::FLUSH_LIMIT === 0) {
                $io->writeln("Flushing @ $i...");
                $this->flush();
            }
        }

        fclose($file);

        $this->flush();

        $io->success("Successfully imported batch file $filename!");

        return Command::SUCCESS;
    }

    private function flush()
    {
        $this->entityManager->flush();
        $this->entityManager->clear();
        gc_collect_cycles();
    }

    private function findOrCreateCompany(string $companyName, string $placeName): Company
    {
        $cacheKey = "$companyName-$placeName";

        // Check local cache
        if (array_key_exists($cacheKey, $this->companyCache)) {
            return $this->companyCache[$cacheKey];
        }

        // Check DB
        $existingPlace = $this->entityManager->getRepository(Company::class)->findOneBy([
            'companyName' => $companyName,
            'placeName' => $placeName,
        ]);

        if ($existingPlace !== null) {
            $this->companyCache[$cacheKey] = $existingPlace;

            return $existingPlace;
        }

        // Otherwise, create a new company and cache
        $newCompany = new Company($companyName, $placeName);
        $this->entityManager->persist($newCompany);
        $this->companyCache[$cacheKey] = $newCompany;

        return $newCompany;
    }

    private function createEntry(int $batchNumber, Company $company, int $depositedAmount, ?int $updatedAmount = null): void
    {
        $entry = new Entry($batchNumber, $company, $depositedAmount, $updatedAmount);
        $this->entityManager->persist($entry);
    }

    private function cleanInput(string $input): string
    {
        return preg_replace('/\s+/', ' ', trim($input));
    }
}
