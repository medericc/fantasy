<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240820194508 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("DELETE FROM player WHERE forename = 'Alexia' AND name = 'Chery'");
    }
    
    public function down(Schema $schema): void
    {
        // If you want to revert this migration, you can insert the record back or leave this method empty
        // $this->addSql("INSERT INTO player (id, team_id, forename, name, rating, selected) VALUES (46, 5, 'Alexia', 'Chery', 0, 0)");
    }
}
