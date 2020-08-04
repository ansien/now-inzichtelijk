<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\BatchEntryAdminLevel;
use App\Entity\BatchEntryCountry;
use App\Entity\BatchEntryPlace;
use App\Repository\BatchEntryAdminLevelRepository;
use App\Repository\BatchEntryCountryRepository;
use App\Repository\BatchEntryPlaceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Geocoder\Exception\Exception;
use Geocoder\Model\AdminLevel;
use Geocoder\Model\Country;
use Geocoder\Provider\GoogleMaps\GoogleMaps;
use Geocoder\Query\GeocodeQuery;
use Geocoder\StatefulGeocoder;
use Http\Adapter\Guzzle6\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class HydratePlacesCommand extends Command
{
    protected static $defaultName = 'app:hydrate-places';

    private BatchEntryPlaceRepository $placeRepository;
    private BatchEntryCountryRepository $countryRepository;
    private BatchEntryAdminLevelRepository $adminLevelRepository;
    private EntityManagerInterface $entityManager;
    private string $googleMapsApiKey;

    public function __construct(
        BatchEntryPlaceRepository $placeRepository,
        BatchEntryCountryRepository $countryRepository,
        BatchEntryAdminLevelRepository $adminLevelRepository,
        EntityManagerInterface $entityManager,
        string $googleMapsApiKey
    ) {
        $this->placeRepository = $placeRepository;
        $this->countryRepository = $countryRepository;
        $this->adminLevelRepository = $adminLevelRepository;
        $this->entityManager = $entityManager;
        $this->googleMapsApiKey = $googleMapsApiKey;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Uses the Google Geocoding API to find more detailed data for all places.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $places = $this->placeRepository->findBy([
            'requiresHydration' => true,
        ]);

        $httpClient = new Client();
        $provider = new GoogleMaps($httpClient, 'europe-west4', $this->googleMapsApiKey);
        $geocoder = new StatefulGeocoder($provider, 'nl');

        foreach ($places as $place) {
            $place->setRequiresHydration(false);

            $this->clearHydration($place);

            try {
                $result = $geocoder->geocodeQuery(GeocodeQuery::create($place->getName()));
            } catch (Exception $e) {
                $io->writeln("Failed @ {$place->getName()}, skipping...");

                continue;
            }

            if ($result->isEmpty() === true) {
                $io->writeln("Empty result @ {$place->getName()}, skipping...");

                continue;
            }

            $firstResult = $result->first();
            $country = $firstResult->getCountry() ? $this->findOrCreateCompany($firstResult->getCountry()) : null;

            $place
                ->setLatitude($firstResult->getCoordinates() ? (string) $firstResult->getCoordinates()->getLatitude() : null)
                ->setLongitude($firstResult->getCoordinates() ? (string) $firstResult->getCoordinates()->getLongitude() : null)
                ->setCountry($country);

            foreach ($firstResult->getAdminLevels() as $adminLevelApiData) {
                $adminLevel = $this->findOrCreateAdminLevel($adminLevelApiData);
                $place->addAdminLevel($adminLevel);
            }

            $this->entityManager->flush();
        }

        $io->success('Finished hydrating places');

        return self::SUCCESS;
    }

    private function clearHydration(BatchEntryPlace $place): void
    {
        $place
            ->setLatitude(null)
            ->setLongitude(null)
            ->setCountry(null);

        foreach ($place->getAdminLevels() as $adminLevel) {
            $place->removeAdminLevel($adminLevel);
        }
    }

    private function findOrCreateCompany(Country $countryApiData): BatchEntryCountry
    {
        $country = $this->countryRepository->findOneBy([
            'name' => $countryApiData->getName(),
            'code' => $countryApiData->getCode(),
        ]);

        if ($country === null) {
            $country = new BatchEntryCountry($countryApiData->getName(), $countryApiData->getCode());
            $this->entityManager->persist($country);
            $this->entityManager->flush();
        }

        return $country;
    }

    private function findOrCreateAdminLevel(AdminLevel $adminLevelApiData): BatchEntryAdminLevel
    {
        $adminLevel = $this->adminLevelRepository->findOneBy([
            'level' => $adminLevelApiData->getLevel(),
            'name' => $adminLevelApiData->getCode(),
            'code' => $adminLevelApiData->getName(),
        ]);

        if ($adminLevel === null) {
            $adminLevel = new BatchEntryAdminLevel(
                $adminLevelApiData->getLevel(),
                $adminLevelApiData->getName(),
                $adminLevelApiData->getCode()
            );
            $this->entityManager->persist($adminLevel);
            $this->entityManager->flush();
        }

        return $adminLevel;
    }
}
