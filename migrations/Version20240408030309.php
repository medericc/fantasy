<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240408030309 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE week DROP FOREIGN KEY FK_5B5A69C08A97161');
        $this->addSql('DROP INDEX IDX_5B5A69C08A97161 ON week');
        $this->addSql('ALTER TABLE week CHANGE league_id_id league_id INT NOT NULL');
        $this->addSql('ALTER TABLE week ADD CONSTRAINT FK_5B5A69C058AFC4DE FOREIGN KEY (league_id) REFERENCES league (id)');
        $this->addSql('CREATE INDEX IDX_5B5A69C058AFC4DE ON week (league_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE week DROP FOREIGN KEY FK_5B5A69C058AFC4DE');
        $this->addSql('DROP INDEX IDX_5B5A69C058AFC4DE ON week');
        $this->addSql('ALTER TABLE week CHANGE league_id league_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE week ADD CONSTRAINT FK_5B5A69C08A97161 FOREIGN KEY (league_id_id) REFERENCES league (id)');
        $this->addSql('CREATE INDEX IDX_5B5A69C08A97161 ON week (league_id_id)');
    }
}
