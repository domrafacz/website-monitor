<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221205204959 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE website_notifier_channel (website_id INT NOT NULL, notifier_channel_id INT NOT NULL, PRIMARY KEY(website_id, notifier_channel_id))');
        $this->addSql('CREATE INDEX IDX_9C7E6BE518F45C82 ON website_notifier_channel (website_id)');
        $this->addSql('CREATE INDEX IDX_9C7E6BE524103478 ON website_notifier_channel (notifier_channel_id)');
        $this->addSql('ALTER TABLE website_notifier_channel ADD CONSTRAINT FK_9C7E6BE518F45C82 FOREIGN KEY (website_id) REFERENCES website (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE website_notifier_channel ADD CONSTRAINT FK_9C7E6BE524103478 FOREIGN KEY (notifier_channel_id) REFERENCES notifier_channel (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notifier_channel ADD name VARCHAR(255) DEFAULT \'noname\' NOT NULL');
        $this->addSql('ALTER TABLE website DROP enabled_notifier_channels');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE website_notifier_channel DROP CONSTRAINT FK_9C7E6BE518F45C82');
        $this->addSql('ALTER TABLE website_notifier_channel DROP CONSTRAINT FK_9C7E6BE524103478');
        $this->addSql('DROP TABLE website_notifier_channel');
        $this->addSql('ALTER TABLE website ADD enabled_notifier_channels TEXT DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN website.enabled_notifier_channels IS \'(DC2Type:simple_array)\'');
        $this->addSql('ALTER TABLE notifier_channel DROP name');
    }
}
