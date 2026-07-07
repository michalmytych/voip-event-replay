<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260707114125 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE voip_events (id BIGINT AUTO_INCREMENT NOT NULL, external_event_id VARCHAR(100) DEFAULT NULL, call_id VARCHAR(100) NOT NULL, type VARCHAR(50) NOT NULL, source VARCHAR(50) NOT NULL, occurred_at DATETIME NOT NULL, received_at DATETIME NOT NULL, payload JSON NOT NULL, sequence_number INT DEFAULT NULL, processing_status VARCHAR(20) NOT NULL, processed_at DATETIME DEFAULT NULL, processing_error LONGTEXT DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE voip_events');
    }
}
