<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240404000637 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE team (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE league ADD id_league_id INT NOT NULL');
        $this->addSql('ALTER TABLE league ADD CONSTRAINT FK_3EB4C318D89A78F9 FOREIGN KEY (id_league_id) REFERENCES team (id)');
        $this->addSql('CREATE INDEX IDX_3EB4C318D89A78F9 ON league (id_league_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE league DROP FOREIGN KEY FK_3EB4C318D89A78F9');
        $this->addSql('DROP TABLE team');
        $this->addSql('DROP INDEX IDX_3EB4C318D89A78F9 ON league');
        $this->addSql('ALTER TABLE league DROP id_league_id');
    }
}
