<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240404022935 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE player_choice (player_id INT NOT NULL, choice_id INT NOT NULL, INDEX IDX_5EABCC4C99E6F5DF (player_id), INDEX IDX_5EABCC4C998666D1 (choice_id), PRIMARY KEY(player_id, choice_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE player_choice ADD CONSTRAINT FK_5EABCC4C99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE player_choice ADD CONSTRAINT FK_5EABCC4C998666D1 FOREIGN KEY (choice_id) REFERENCES choice (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE player_choice DROP FOREIGN KEY FK_5EABCC4C99E6F5DF');
        $this->addSql('ALTER TABLE player_choice DROP FOREIGN KEY FK_5EABCC4C998666D1');
        $this->addSql('DROP TABLE player_choice');
    }
}
