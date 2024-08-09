<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240809091027 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE choice_player DROP FOREIGN KEY FK_7D6EF686998666D1');
        $this->addSql('ALTER TABLE choice_player DROP FOREIGN KEY FK_7D6EF68699E6F5DF');
        $this->addSql('DROP TABLE choice_player');
        $this->addSql('ALTER TABLE choice ADD player_id INT NOT NULL');
        $this->addSql('ALTER TABLE choice ADD CONSTRAINT FK_C1AB5A9299E6F5DF FOREIGN KEY (player_id) REFERENCES player (id)');
        $this->addSql('CREATE INDEX IDX_C1AB5A9299E6F5DF ON choice (player_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE choice_player (choice_id INT NOT NULL, player_id INT NOT NULL, INDEX IDX_7D6EF686998666D1 (choice_id), INDEX IDX_7D6EF68699E6F5DF (player_id), PRIMARY KEY(choice_id, player_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE choice_player ADD CONSTRAINT FK_7D6EF686998666D1 FOREIGN KEY (choice_id) REFERENCES choice (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE choice_player ADD CONSTRAINT FK_7D6EF68699E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE choice DROP FOREIGN KEY FK_C1AB5A9299E6F5DF');
        $this->addSql('DROP INDEX IDX_C1AB5A9299E6F5DF ON choice');
        $this->addSql('ALTER TABLE choice DROP player_id');
    }
}
