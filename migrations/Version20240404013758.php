<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240404013758 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE game (id INT AUTO_INCREMENT NOT NULL, team_home_id INT DEFAULT NULL, team_away_id INT NOT NULL, UNIQUE INDEX UNIQ_232B318CD7B4B9AB (team_home_id), UNIQUE INDEX UNIQ_232B318C729679A8 (team_away_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318CD7B4B9AB FOREIGN KEY (team_home_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318C729679A8 FOREIGN KEY (team_away_id) REFERENCES team (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318CD7B4B9AB');
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318C729679A8');
        $this->addSql('DROP TABLE game');
    }
}
