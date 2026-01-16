<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251208162140 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'No changes needed – media foreign key and indexes already match the mapping.';
    }

    public function up(Schema $schema): void
    {
        // Nothing to do – schema already in sync
    }

    public function down(Schema $schema): void
    {
        // Nothing to revert
    }
}
