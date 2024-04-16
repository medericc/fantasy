<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240416221156 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE choice DROP FOREIGN KEY FK_C1AB5A92B4EF57D4');
        $this->addSql('DROP INDEX UNIQ_C1AB5A92B4EF57D4 ON choice');
        $this->addSql('ALTER TABLE choice CHANGE week_id_id week_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE choice ADD CONSTRAINT FK_C1AB5A92C86F3B2F FOREIGN KEY (week_id) REFERENCES week (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C1AB5A92C86F3B2F ON choice (week_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE choice DROP FOREIGN KEY FK_C1AB5A92C86F3B2F');
        $this->addSql('DROP INDEX UNIQ_C1AB5A92C86F3B2F ON choice');
        $this->addSql('ALTER TABLE choice CHANGE week_id week_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE choice ADD CONSTRAINT FK_C1AB5A92B4EF57D4 FOREIGN KEY (week_id_id) REFERENCES week (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C1AB5A92B4EF57D4 ON choice (week_id_id)');
    }
}
