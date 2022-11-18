<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221118144415 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE website_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE website (id INT NOT NULL, owner_id INT NOT NULL, url VARCHAR(255) NOT NULL, request_method VARCHAR(100) NOT NULL, max_redirects SMALLINT NOT NULL, timeout SMALLINT NOT NULL, last_status SMALLINT NOT NULL, last_check TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, cert_expiry_time TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, frequency INT NOT NULL, enabled BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_476F5DE77E3C61F9 ON website (owner_id)');
        $this->addSql('ALTER TABLE website ADD CONSTRAINT FK_476F5DE77E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE website_id_seq CASCADE');
        $this->addSql('ALTER TABLE website DROP CONSTRAINT FK_476F5DE77E3C61F9');
        $this->addSql('DROP TABLE website');
    }
}
