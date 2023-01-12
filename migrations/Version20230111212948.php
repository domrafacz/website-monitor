<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230111212948 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE response_log_archive_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE response_log_archive (id INT NOT NULL, website_id INT NOT NULL, date DATE NOT NULL, average_response_time INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4DE87F8418F45C82 ON response_log_archive (website_id)');
        $this->addSql('COMMENT ON COLUMN response_log_archive.date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE response_log_archive ADD CONSTRAINT FK_4DE87F8418F45C82 FOREIGN KEY (website_id) REFERENCES website (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE response_log_archive_id_seq CASCADE');
        $this->addSql('ALTER TABLE response_log_archive DROP CONSTRAINT FK_4DE87F8418F45C82');
        $this->addSql('DROP TABLE response_log_archive');
    }
}
