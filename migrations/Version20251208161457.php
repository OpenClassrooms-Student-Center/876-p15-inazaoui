<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251208161457 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix migration after entity refactoring (no change to media.user_id)';
    }

    public function up(Schema $schema): void
    {
        // media.user_id exists already → do NOT recreate it
        // remove all incorrect auto-generated SQL regarding media.user_id

        // Only execute valid schema updates
        $this->addSql('ALTER TABLE user CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');

        // Ensure unique index name on email
        $this->addSql('ALTER TABLE user RENAME INDEX email TO UNIQ_8D93D649E7927C74');
    }

    public function down(Schema $schema): void
    {
        // revert the index name
        $this->addSql('ALTER TABLE user RENAME INDEX UNIQ_8D93D649E7927C74 TO email');

        // revert created_at definition
        $this->addSql('ALTER TABLE user CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP');
    }
}
