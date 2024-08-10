<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240810063101 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Suppression des anciens index
        $this->addSql('ALTER TABLE user DROP INDEX uniq_identifier_email');
        $this->addSql('ALTER TABLE user DROP INDEX uniq_identifier_pseudo');
        
        // Création des nouveaux index avec les nouveaux noms
        $this->addSql('ALTER TABLE user ADD UNIQUE INDEX UNIQ_8D93D649E7927C74 (email_column_name)');
        $this->addSql('ALTER TABLE user ADD UNIQUE INDEX UNIQ_8D93D64986CC499D (pseudo_column_name)');
    }

    public function down(Schema $schema): void
    {
        // Suppression des nouveaux index
        $this->addSql('ALTER TABLE user DROP INDEX UNIQ_8D93D649E7927C74');
        $this->addSql('ALTER TABLE user DROP INDEX UNIQ_8D93D64986CC499D');
        
        // Récréation des anciens index avec les anciens noms
        $this->addSql('ALTER TABLE user ADD UNIQUE INDEX uniq_identifier_email (email_column_name)');
        $this->addSql('ALTER TABLE user ADD UNIQUE INDEX uniq_identifier_pseudo (pseudo_column_name)');
    }
}
