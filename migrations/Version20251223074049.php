<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251223074049 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ensure proper ON DELETE CASCADE on album.user_id and media.user_id / album_id (idempotent)';
    }

    public function up(Schema $schema): void
    {
        // ---- ALBUM -> USER --------------------------------------------------
        $this->addSql("
            SET @fk_album_user := (
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'album'
                  AND COLUMN_NAME = 'user_id'
                  AND REFERENCED_TABLE_NAME = 'user'
                LIMIT 1
            );
        ");
        $this->addSql("
            SET @sql := IF(
                @fk_album_user IS NOT NULL,
                CONCAT('ALTER TABLE album DROP FOREIGN KEY ', @fk_album_user),
                'SELECT 1'
            );
        ");
        $this->addSql('PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;');

        $this->addSql("
            ALTER TABLE album
            ADD CONSTRAINT FK_ALBUM_USER
            FOREIGN KEY (user_id) REFERENCES user (id)
            ON DELETE CASCADE
        ");

        // ---- MEDIA -> USER --------------------------------------------------
        $this->addSql("
            SET @fk_media_user := (
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'media'
                  AND COLUMN_NAME = 'user_id'
                  AND REFERENCED_TABLE_NAME = 'user'
                LIMIT 1
            );
        ");
        $this->addSql("
            SET @sql := IF(
                @fk_media_user IS NOT NULL,
                CONCAT('ALTER TABLE media DROP FOREIGN KEY ', @fk_media_user),
                'SELECT 1'
            );
        ");
        $this->addSql('PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;');

        $this->addSql("
            ALTER TABLE media
            ADD CONSTRAINT FK_MEDIA_USER
            FOREIGN KEY (user_id) REFERENCES user (id)
            ON DELETE CASCADE
        ");

        // ---- MEDIA -> ALBUM -------------------------------------------------
        $this->addSql("
            SET @fk_media_album := (
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'media'
                  AND COLUMN_NAME = 'album_id'
                  AND REFERENCED_TABLE_NAME = 'album'
                LIMIT 1
            );
        ");
        $this->addSql("
            SET @sql := IF(
                @fk_media_album IS NOT NULL,
                CONCAT('ALTER TABLE media DROP FOREIGN KEY ', @fk_media_album),
                'SELECT 1'
            );
        ");
        $this->addSql('PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;');

        $this->addSql("
            ALTER TABLE media
            ADD CONSTRAINT FK_MEDIA_ALBUM
            FOREIGN KEY (album_id) REFERENCES album (id)
            ON DELETE CASCADE
        ");

        // ---- DESCRIPTION NULLABLE ------------------------------------------
        $this->addSql("
            ALTER TABLE media
            MODIFY description VARCHAR(255) NULL
        ");
    }

    public function down(Schema $schema): void
    {
        // volontairement vide : rollback dangereux sur FK en prod
    }
}
