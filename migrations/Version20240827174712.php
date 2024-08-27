<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240827174712 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute Soana LUCET dans l\'équipe avec team_id 5';
    }

    public function up(Schema $schema): void
    {
        // Insertion de Soana LUCET dans la base de données
        $this->addSql('INSERT INTO player (team_id, forename, name, rating, selected) VALUES (5, "Soana", "LUCET", 0, 0)');
    }

    public function down(Schema $schema): void
    {
    }
}
