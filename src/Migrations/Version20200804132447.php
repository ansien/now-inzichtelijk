<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200804132447 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE batch_entry_admin_level (id INT AUTO_INCREMENT NOT NULL, level INT NOT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, INDEX name_idx (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE batch_entry_country (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE batch_entry_place_batch_entry_admin_level (batch_entry_place_id INT NOT NULL, batch_entry_admin_level_id INT NOT NULL, INDEX IDX_106EC58996D7464D (batch_entry_place_id), INDEX IDX_106EC589A39FF7B7 (batch_entry_admin_level_id), PRIMARY KEY(batch_entry_place_id, batch_entry_admin_level_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE batch_entry_place_batch_entry_admin_level ADD CONSTRAINT FK_106EC58996D7464D FOREIGN KEY (batch_entry_place_id) REFERENCES batch_entry_place (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE batch_entry_place_batch_entry_admin_level ADD CONSTRAINT FK_106EC589A39FF7B7 FOREIGN KEY (batch_entry_admin_level_id) REFERENCES batch_entry_admin_level (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE batch_entry_place ADD country_id INT DEFAULT NULL, ADD latitude NUMERIC(10, 8) DEFAULT NULL, ADD longitude VARCHAR(255) DEFAULT NULL, ADD hydration_attempted TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE batch_entry_place ADD CONSTRAINT FK_41C7296F92F3E70 FOREIGN KEY (country_id) REFERENCES batch_entry_country (id)');
        $this->addSql('CREATE INDEX IDX_41C7296F92F3E70 ON batch_entry_place (country_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE batch_entry_place_batch_entry_admin_level DROP FOREIGN KEY FK_106EC589A39FF7B7');
        $this->addSql('ALTER TABLE batch_entry_place DROP FOREIGN KEY FK_41C7296F92F3E70');
        $this->addSql('DROP TABLE batch_entry_admin_level');
        $this->addSql('DROP TABLE batch_entry_country');
        $this->addSql('DROP TABLE batch_entry_place_batch_entry_admin_level');
        $this->addSql('DROP INDEX IDX_41C7296F92F3E70 ON batch_entry_place');
        $this->addSql('ALTER TABLE batch_entry_place DROP country_id, DROP latitude, DROP longitude, DROP hydration_attempted');
    }
}
