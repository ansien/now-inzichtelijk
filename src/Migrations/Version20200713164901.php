<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200713164901 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE INDEX name_idx ON batch_entry_place (name)');
        $this->addSql('CREATE INDEX company_name_idx ON first_batch_entry (company_name)');
        $this->addSql('CREATE INDEX amount_idx ON first_batch_entry (amount)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX name_idx ON batch_entry_place');
        $this->addSql('DROP INDEX company_name_idx ON first_batch_entry');
        $this->addSql('DROP INDEX amount_idx ON first_batch_entry');
    }
}
