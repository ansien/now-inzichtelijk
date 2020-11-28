<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201128152805 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE api_request (id INT AUTO_INCREMENT NOT NULL, endpoint VARCHAR(255) NOT NULL, query LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE batch_entry_place (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, INDEX name_idx (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE first_batch_entry (id INT AUTO_INCREMENT NOT NULL, place_id INT DEFAULT NULL, company_name VARCHAR(255) NOT NULL, amount INT NOT NULL, INDEX IDX_98461BD5DA6A219 (place_id), INDEX company_name_idx (company_name), INDEX amount_idx (amount), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE second_batch_entry (id INT AUTO_INCREMENT NOT NULL, place_id INT DEFAULT NULL, company_name VARCHAR(255) NOT NULL, amount INT NOT NULL, INDEX IDX_6F568A09DA6A219 (place_id), INDEX company_name_idx (company_name), INDEX amount_idx (amount), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE first_batch_entry ADD CONSTRAINT FK_98461BD5DA6A219 FOREIGN KEY (place_id) REFERENCES batch_entry_place (id)');
        $this->addSql('ALTER TABLE second_batch_entry ADD CONSTRAINT FK_6F568A09DA6A219 FOREIGN KEY (place_id) REFERENCES batch_entry_place (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE first_batch_entry DROP FOREIGN KEY FK_98461BD5DA6A219');
        $this->addSql('ALTER TABLE second_batch_entry DROP FOREIGN KEY FK_6F568A09DA6A219');
        $this->addSql('DROP TABLE api_request');
        $this->addSql('DROP TABLE batch_entry_place');
        $this->addSql('DROP TABLE first_batch_entry');
        $this->addSql('DROP TABLE second_batch_entry');
    }
}
