<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260124203227 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" ADD invitation_token VARCHAR(64) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD invitation_expired_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ALTER password DROP NOT NULL');
        $this->addSql('COMMENT ON COLUMN "user".invitation_expired_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D64933FC351A ON "user" (invitation_token)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX UNIQ_8D93D64933FC351A');
        $this->addSql('ALTER TABLE "user" DROP invitation_token');
        $this->addSql('ALTER TABLE "user" DROP invitation_expired_at');
        $this->addSql('ALTER TABLE "user" ALTER password SET NOT NULL');
    }
}
