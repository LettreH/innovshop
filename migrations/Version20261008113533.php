<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261008113533 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6EEAA67DF55AE19E ON commande (numero)');
        $this->addSql('CREATE INDEX idx_commande_statut ON commande (statut)');
        $this->addSql('CREATE INDEX idx_produit_date_ajout ON produit (date_ajout)');
        $this->addSql('CREATE INDEX idx_produit_ala_une ON produit (ala_une)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_6EEAA67DF55AE19E ON commande');
        $this->addSql('DROP INDEX idx_commande_statut ON commande');
        $this->addSql('DROP INDEX idx_produit_date_ajout ON produit');
        $this->addSql('DROP INDEX idx_produit_ala_une ON produit');
    }
}
