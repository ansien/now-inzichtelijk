# NOW Regeling Inzichtelijk Website
> Code achter de website: https://now-inzichtelijk.nl.

## Ontwikkelomgeving vereisten
- PHP 7.4 (incl. Composer)
- MySQL
- Node.js
- Yarn / npm
- (Optioneel) Redis

## Installatie

1. Maak een `.env.local` bestand gebaseerd op het `.env` bestand met jouw MySQL database gegevens.
2. Maak een `.php_cs` bestand met dezelfde inhoud als het `.php_cs.dist` bestand.
3. Installeer alle dependencies via composer: `composer install`.
4. Run de lokale ontwikkel server: `symfony serve`.
5. (Optioneel) Draai de [Symfony Encore](https://symfony.com/doc/current/frontend.html) watcher via `yarn encore dev --watch` om frontend (CSS/JS) aanpassingen te compileren.

# Handige commands
```sh
# Convert all batches from TXT to CSV
php bin/console app:convert-first-batch && php bin/console app:convert-second-batch

# Clear db and re-import all batches
php bin/console doctrine:schema:drop --full-database --force && php bin/console doctrine:schema:update --force && php bin/console app:import-first-batch && php bin/console app:import-second-batch

# Deploy
./vendor/bin/dep deploy production
```

## Bijdrage doen

1. Fork het project
2. Maak een feature branch (`git checkout -b feature/fooBar`)
3. Commit je aanpassingen (`git commit -am 'x toegevoegd'`)
4. Push naar je branch (`git push origin feature/fooBar`)
5. Maak een pull request