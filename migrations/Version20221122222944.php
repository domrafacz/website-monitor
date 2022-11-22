<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221122222944 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE response_log_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE response_log (id INT NOT NULL, website_id INT NOT NULL, status SMALLINT NOT NULL, time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, response_time INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_92CCB2EE18F45C82 ON response_log (website_id)');
        $this->addSql('ALTER TABLE response_log ADD CONSTRAINT FK_92CCB2EE18F45C82 FOREIGN KEY (website_id) REFERENCES website (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE response_log_id_seq CASCADE');
        $this->addSql('ALTER TABLE response_log DROP CONSTRAINT FK_92CCB2EE18F45C82');
        $this->addSql('DROP TABLE response_log');
    }
}
