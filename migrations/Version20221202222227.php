<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221202222227 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE notifier_channel_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE notifier_channel (id INT NOT NULL, owner_id INT NOT NULL, type SMALLINT NOT NULL, options JSON DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2F95AF8E7E3C61F9 ON notifier_channel (owner_id)');
        $this->addSql('ALTER TABLE notifier_channel ADD CONSTRAINT FK_2F95AF8E7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE website ADD enabled_notifier_channels TEXT DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN website.enabled_notifier_channels IS \'(DC2Type:simple_array)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE notifier_channel_id_seq CASCADE');
        $this->addSql('ALTER TABLE notifier_channel DROP CONSTRAINT FK_2F95AF8E7E3C61F9');
        $this->addSql('DROP TABLE notifier_channel');
        $this->addSql('ALTER TABLE website DROP enabled_notifier_channels');
    }
}
