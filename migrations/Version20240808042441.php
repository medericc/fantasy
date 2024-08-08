<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240808042441 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE player ADD slug VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_98197A65989D9B62 ON player (slug)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_PSEUDO ON user (pseudo)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_98197A65989D9B62 ON player');
        $this->addSql('ALTER TABLE player DROP slug');
        $this->addSql('DROP INDEX UNIQ_IDENTIFIER_PSEUDO ON user');
    }
}
