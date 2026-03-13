<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250309000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create notification table for Notification Service';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE notification (
            id VARCHAR(36) NOT NULL,
            user_id VARCHAR(255) NOT NULL,
            recipient_value VARCHAR(512) NOT NULL,
            recipient_channel VARCHAR(100) NOT NULL,
            subject VARCHAR(500) NOT NULL,
            body TEXT NOT NULL,
            status VARCHAR(50) NOT NULL,
            type VARCHAR(50) NOT NULL,
            channels JSON NOT NULL,
            delivery_attempts JSON NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            sent_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX idx_notification_user_created ON notification (user_id, created_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE notification');
    }
}
