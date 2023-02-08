<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230208093723 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE etat CHANGE libelle libelle VARCHAR(180) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_55CAF762A4D60759 ON etat (libelle)');
        $this->addSql('ALTER TABLE lieu CHANGE latitude latitude DOUBLE PRECISION DEFAULT NULL, CHANGE longitude longitude DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE participant CHANGE telephone telephone VARCHAR(10) NOT NULL');
        $this->addSql('ALTER TABLE sortie CHANGE duree duree INT NOT NULL, CHANGE date_limite_inscription date_limite_inscription DATE NOT NULL');
        $this->addSql('ALTER TABLE ville CHANGE code_postal code_postal VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_55CAF762A4D60759 ON etat');
        $this->addSql('ALTER TABLE etat CHANGE libelle libelle VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE lieu CHANGE latitude latitude DOUBLE PRECISION NOT NULL, CHANGE longitude longitude DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE participant CHANGE telephone telephone INT NOT NULL');
        $this->addSql('ALTER TABLE sortie CHANGE duree duree TIME NOT NULL, CHANGE date_limite_inscription date_limite_inscription DATETIME NOT NULL');
        $this->addSql('ALTER TABLE ville CHANGE code_postal code_postal INT NOT NULL');
    }
}
