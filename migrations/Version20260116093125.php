<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260116093125 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
         // 1) Add roles with a temporary default so existing rows are valid
        $this->addSql('ALTER TABLE "user" ADD roles JSON NOT NULL DEFAULT \'[]\'');

        // 2) Copy admin -> roles
        $this->addSql("
        UPDATE \"user\"
        SET roles = CASE
            WHEN admin = true THEN '[\"ROLE_ADMIN\"]'::json
            ELSE '[]'::json
        END
    ");

        $this->addSql('ALTER TABLE "user" ADD password VARCHAR(255) DEFAULT NULL');

        $this->addSql('ALTER TABLE "user" ADD is_active BOOLEAN NOT NULL DEFAULT TRUE');

        $this->addSql('ALTER TABLE "user" DROP admin');

        $this->addSql('ALTER TABLE "user" ALTER COLUMN roles DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "user" ADD admin BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE "user" DROP roles');
        $this->addSql('ALTER TABLE "user" DROP password');
        $this->addSql('ALTER TABLE "user" DROP is_active');
        $this->addSql('ALTER TABLE "user" ALTER COLUMN is_active DROP DEFAULT');
    }
}
