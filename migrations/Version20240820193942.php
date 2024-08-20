<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240820193942 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // Ceci exécutera la requête SQL pour renommer l'équipe
        $this->addSql("UPDATE team SET name = 'BB La Tronche' WHERE name = 'BB La Tronche-Meylan'");
    }

    public function down(Schema $schema): void
    {
        // Ceci annulera la migration si besoin
        $this->addSql("UPDATE team SET name = 'BB La Tronche-Meylan' WHERE name = 'BB La Tronche'");
    }
}
