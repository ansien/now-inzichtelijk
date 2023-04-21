# NOW Regeling Inzichtelijk Website
> Code achter de website: https://now-inzichtelijk.nl.

## Ontwikkelomgeving vereisten
- PHP 8.2 (incl. Composer)
- MySQL
- Node.js
- Yarn / npm
- (Optioneel) Redis

## Installatie

1. Maak een `.env.local` bestand gebaseerd op het `.env` bestand met jouw MySQL database gegevens.
2. Installeer alle backend dependencies via Composer: `composer install`.
3. Installeer alle frontend dependencies via Yarn: `yarn install`.
4. Draai [Symfony Encore](https://symfony.com/doc/current/frontend.html) met `yarn encore dev` om frontend assets te builden.
5. Run de lokale ontwikkel server: `symfony serve`.

## Handige commands
```sh
# Drop de database en herimporteer de CSV bestanden
php bin/console doctrine:schema:drop --full-database --force && php bin/console doctrine:schema:update --force && php bin/console app:import-batch 1 && php bin/console app:import-batch 2 && php bin/console app:import-batch 3 && php bin/console app:import-batch 4 && php bin/console app:import-batch 5 && php bin/console app:import-batch 6 && php bin/console app:import-batch 7 && php bin/console app:import-batch 8

# Deployer
./vendor/bin/dep deploy prod

# Ansible
ansible-playbook playbook.yml --ask-vault-pass
```

## Bijdrage doen

1. Fork het project
2. Maak een feature branch (`git checkout -b feature/fooBar`)
3. Commit je aanpassingen (`git commit -am 'x toegevoegd'`)
4. Push naar je branch (`git push origin feature/fooBar`)
5. Maak een pull request
