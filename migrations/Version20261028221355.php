<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261028221355 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Biblioteca por defecto para no perder los datos/usuarios ya existentes.
        $this->addSql('CREATE TABLE biblioteca (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, creado_en DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql("INSERT INTO biblioteca (nombre, creado_en) VALUES ('Biblioteca Humberto Cárdenas Trigos', NOW())");

        $this->addSql('ALTER TABLE remision ADD biblioteca_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD biblioteca_id INT DEFAULT NULL');

        $this->addSql("UPDATE remision SET biblioteca_id = (SELECT id FROM biblioteca WHERE nombre = 'Biblioteca Humberto Cárdenas Trigos' LIMIT 1)");
        $this->addSql("UPDATE user SET biblioteca_id = (SELECT id FROM biblioteca WHERE nombre = 'Biblioteca Humberto Cárdenas Trigos' LIMIT 1)");

        $this->addSql('ALTER TABLE remision MODIFY biblioteca_id INT NOT NULL');
        $this->addSql('ALTER TABLE user MODIFY biblioteca_id INT NOT NULL');

        $this->addSql('ALTER TABLE remision ADD CONSTRAINT FK_472E7946A5EDAE9 FOREIGN KEY (biblioteca_id) REFERENCES biblioteca (id)');
        $this->addSql('CREATE INDEX IDX_472E7946A5EDAE9 ON remision (biblioteca_id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6496A5EDAE9 FOREIGN KEY (biblioteca_id) REFERENCES biblioteca (id)');
        $this->addSql('CREATE INDEX IDX_8D93D6496A5EDAE9 ON user (biblioteca_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE biblioteca');
        $this->addSql('ALTER TABLE remision DROP FOREIGN KEY FK_472E7946A5EDAE9');
        $this->addSql('DROP INDEX IDX_472E7946A5EDAE9 ON remision');
        $this->addSql('ALTER TABLE remision DROP biblioteca_id');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6496A5EDAE9');
        $this->addSql('DROP INDEX IDX_8D93D6496A5EDAE9 ON user');
        $this->addSql('ALTER TABLE user DROP biblioteca_id');
    }
}
