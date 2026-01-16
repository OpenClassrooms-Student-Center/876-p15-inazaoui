<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration corrigée manuellement :
 * - La colonne album.user_id existe déjà en base
 * - Toute tentative de création/suppression est supprimée
 * - Cette migration est rendue idempotente
 */
final class Version20251220184012 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix invalid auto-generated migration (duplicate user_id) and enforce media.description NOT NULL';
    }

    public function up(Schema $schema): void
    {
        // La colonne album.user_id existe déjà → NE RIEN FAIRE
        // On conserve uniquement la modification valide sur media.description

        $this->addSql(
            'ALTER TABLE media CHANGE description description VARCHAR(255) NOT NULL'
        );
    }

    public function down(Schema $schema): void
    {
        // Revert uniquement ce qui a été fait dans up()

        $this->addSql(
            'ALTER TABLE media CHANGE description description VARCHAR(255) DEFAULT NULL'
        );
    }
}
