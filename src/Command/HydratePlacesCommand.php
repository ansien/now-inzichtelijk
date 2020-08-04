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

        $additions = [
            'BEST' => 'Nederland',
            'BARCELONA' => 'Spanje',
            'MALDEN' => 'Nederland',
            'DORST' => 'Nederland',
            'MADE' => 'Nederland',
            'GEFFEN' => 'Nederland',
            'VEEN' => 'Nederland',
            'LINDEN' => 'Nederland',
            'HAAR' => 'Duitsland',
            'WARNS' => 'Nederland',
            'HANDEL' => 'Nederland',
            'HOOGLAND' => 'Nederland',
            'HERTEN' => 'Nederland',
            'ERICA' => 'Nederland',
            'HORN' => 'Nederland',
            'HANK' => 'Nederland',
            'ELIM' => 'Nederland',
            'THORN' => 'Maasgauw Limburg Nederland',
            'WARTEN' => 'Nederland',
            'AMERICA' => 'Nederland',
            'RUTTEN' => 'Nederland',
            'LENGEL' => 'Nederland',
            'HERTS' => 'England',
            'NUTTER' => 'Nederland',
            'RHA' => 'Nederland',
            'ERM' => 'Nederland',
            'WELL LB' => 'Nederland',
            'AMEN' => 'Nederland',
            'NUIS' => 'Nederland',
            'HELDEN' => 'Nederland',
            'HALL' => 'Gelderland Nederland',
            'LOON' => 'Assen Nederland',
            'MUSSEL' => 'Groningen Nederland',
            'PEER' => 'Belgie',
            'EEN' => 'Drenthe Nederland',
            'SCHERMERHORN' => 'Nederland',
            'PETTEN' => 'Nederland',
            'BALLOO' => 'Nederland',
            'INGBER' => 'Nederland',
            'EST' => 'Gelderland Nederland',
            'ALTORF' => 'Frankrijk',
            'ERIKA' => 'Drenthe Nederland',
            'BAARD' => 'Nederland',
            'BRISTOL' => 'England',
            'MERING' => 'Belgie',
            'LIES' => 'Noord-Brabant Nederland',
            'WANGEN' => 'Duitsland',
            'GRAFT' => 'Nederland',
            'EXETER' => 'England',
            'WECKER' => 'England',
            'LINDE DR' => 'Drenthe Nederland',
            'BROEKHUIZEN DR' => 'Drenthe Nederland',
            'DEMEN' => 'Noord-Brabant Nederland',
            'NOTTER' => 'Nederland',
            'HOBOKEN' => 'Belgie',
            'PIETA' => 'Malta',
            'ERP' => 'Nederland',
            'MEER' => 'Antwerpen Belgie',
            'BAAK' => 'Nederland',
            'MILL' => 'Nederland',
            'WETERING' => 'Nederland',
            'PHILIPPINE' => 'Zeeland Nederland',
            'OFFENBACH' => 'Duitsland',
            'HEM' => 'Nederland',
            'ZAIO' => 'Marokko',
            'PEEBLES' => 'Schotland',
        ];

        $replacements = [
            'BAAR' => 'Nederhorst Den Berg',
            'BALLINGSLOV' => 'Ballingslöv Zweden',
            'GROSSENZELL' => 'Groebenzell Duitsland',
            'HOLTE RUNENSDAL' => 'Amsterdam',
            'DE POL' => 'Sint Geertruid, Nederland',
        ];

        $httpClient = new Client();
        $provider = new GoogleMaps($httpClient, 'europe-west4', $this->googleMapsApiKey);
        $geocoder = new StatefulGeocoder($provider, 'nl');

        foreach ($places as $place) {
            $place->setRequiresHydration(false);

            $this->clearHydration($place);

            $placeName = $place->getName();

            if (array_key_exists(strtoupper($placeName), $additions) === true) {
                $placeName .= ' ' . $additions[strtoupper($placeName)];
            } elseif (array_key_exists(strtoupper($placeName), $replacements) === true) {
                $placeName = $replacements[strtoupper($placeName)];
            }

            try {
                $result = $geocoder->geocodeQuery(GeocodeQuery::create($placeName));
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

        return 0;
    }

    private function clearHydration(BatchEntryPlace $place): void
    {
        $country = $place->getCountry();

        $place
            ->setLatitude(null)
            ->setLongitude(null)
            ->setCountry(null);

        if ($country !== null && $country->getPlaces()->count() <= 0) {
            $this->entityManager->remove($country);
            $this->entityManager->flush();
        }

        foreach ($place->getAdminLevels() as $adminLevel) {
            $place->removeAdminLevel($adminLevel);

            if ($adminLevel->getPlaces()->count() <= 0) {
                $this->entityManager->remove($adminLevel);
                $this->entityManager->flush();
            }
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
