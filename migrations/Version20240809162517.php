<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240809162517 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE player_rate (id INT AUTO_INCREMENT NOT NULL, player_id INT NOT NULL, week_id INT NOT NULL, rate DOUBLE PRECISION NOT NULL, INDEX IDX_7DF66F0A99E6F5DF (player_id), INDEX IDX_7DF66F0AC86F3B2F (week_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE player_rate ADD CONSTRAINT FK_7DF66F0A99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE player_rate ADD CONSTRAINT FK_7DF66F0AC86F3B2F FOREIGN KEY (week_id) REFERENCES week (id)');
        $this->addSql('ALTER TABLE choice ADD points DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE choice ADD CONSTRAINT FK_C1AB5A9299E6F5DF FOREIGN KEY (player_id) REFERENCES player (id)');
        $this->addSql('CREATE INDEX IDX_C1AB5A9299E6F5DF ON choice (player_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE player_rate DROP FOREIGN KEY FK_7DF66F0A99E6F5DF');
        $this->addSql('ALTER TABLE player_rate DROP FOREIGN KEY FK_7DF66F0AC86F3B2F');
        $this->addSql('DROP TABLE player_rate');
        $this->addSql('ALTER TABLE choice DROP FOREIGN KEY FK_C1AB5A9299E6F5DF');
        $this->addSql('DROP INDEX IDX_C1AB5A9299E6F5DF ON choice');
        $this->addSql('ALTER TABLE choice DROP points');
    }
}
