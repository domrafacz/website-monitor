<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221124200916 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE downtime_log_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE downtime_log (id INT NOT NULL, website_id INT NOT NULL, start_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, end_time TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, initial_error JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2305EBF718F45C82 ON downtime_log (website_id)');
        $this->addSql('COMMENT ON COLUMN downtime_log.start_time IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN downtime_log.end_time IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE downtime_log ADD CONSTRAINT FK_2305EBF718F45C82 FOREIGN KEY (website_id) REFERENCES website (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE downtime_log_id_seq CASCADE');
        $this->addSql('ALTER TABLE downtime_log DROP CONSTRAINT FK_2305EBF718F45C82');
        $this->addSql('DROP TABLE downtime_log');
    }
}
