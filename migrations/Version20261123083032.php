<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261123083032 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `user` ADD roles JSON DEFAULT NULL, ADD password VARCHAR(255) DEFAULT NULL');
        $this->addSql("UPDATE `user` SET roles = IF(admin = 1, JSON_ARRAY('ROLE_ADMIN'), JSON_ARRAY())");
        $this->addSql('ALTER TABLE `user` MODIFY roles JSON NOT NULL, DROP admin');
        // $this->addSql('ALTER TABLE user ADD roles JSON NOT NULL, ADD password VARCHAR(255) DEFAULT NULL, DROP `admin`, CHANGE name name VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `user` ADD admin TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql("UPDATE `user` SET admin = 1 WHERE JSON_CONTAINS(roles, '\"ROLE_ADMIN\"')");
        $this->addSql('ALTER TABLE `user` DROP roles, DROP password');
        // $this->addSql('ALTER TABLE `user` ADD `admin` TINYINT(1) NOT NULL, DROP roles, DROP password, CHANGE name name VARCHAR(255) NOT NULL');
    }
}
