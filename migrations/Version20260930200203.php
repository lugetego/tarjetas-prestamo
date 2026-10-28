<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260930200203 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE remision (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, prefijo_adquisicion VARCHAR(50) NOT NULL, archivo_original VARCHAR(255) DEFAULT NULL, creado_en DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tarjeta (id INT AUTO_INCREMENT NOT NULL, adquision VARCHAR(100) DEFAULT NULL, titulo LONGTEXT DEFAULT NULL, autor VARCHAR(255) DEFAULT NULL, sistema VARCHAR(100) DEFAULT NULL, clasificacion VARCHAR(100) DEFAULT NULL, remision_id INT NOT NULL, INDEX IDX_AE90B786336FA023 (remision_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_USER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE tarjeta ADD CONSTRAINT FK_AE90B786336FA023 FOREIGN KEY (remision_id) REFERENCES remision (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tarjeta DROP FOREIGN KEY FK_AE90B786336FA023');
        $this->addSql('DROP TABLE remision');
        $this->addSql('DROP TABLE tarjeta');
        $this->addSql('DROP TABLE user');
    }
}
