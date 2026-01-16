<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251220192113 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make media.description NOT NULL';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'ALTER TABLE media CHANGE description description VARCHAR(255) NOT NULL'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            'ALTER TABLE media CHANGE description description VARCHAR(255) DEFAULT NULL'
        );
    }
}
