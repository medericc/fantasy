<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240404011144 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE league DROP FOREIGN KEY FK_3EB4C318D89A78F9');
        $this->addSql('DROP INDEX IDX_3EB4C318D89A78F9 ON league');
        $this->addSql('ALTER TABLE league DROP id_league_id');
        $this->addSql('ALTER TABLE team ADD league_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61F8A97161 FOREIGN KEY (league_id_id) REFERENCES league (id)');
        $this->addSql('CREATE INDEX IDX_C4E0A61F8A97161 ON team (league_id_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE league ADD id_league_id INT NOT NULL');
        $this->addSql('ALTER TABLE league ADD CONSTRAINT FK_3EB4C318D89A78F9 FOREIGN KEY (id_league_id) REFERENCES team (id)');
        $this->addSql('CREATE INDEX IDX_3EB4C318D89A78F9 ON league (id_league_id)');
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61F8A97161');
        $this->addSql('DROP INDEX IDX_C4E0A61F8A97161 ON team');
        $this->addSql('ALTER TABLE team DROP league_id_id');
    }
}
