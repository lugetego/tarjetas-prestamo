<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261111185410 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE inicio_sesion (id INT AUTO_INCREMENT NOT NULL, fecha DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_D02961E2A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE inicio_sesion ADD CONSTRAINT FK_D02961E2A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inicio_sesion DROP FOREIGN KEY FK_D02961E2A76ED395');
        $this->addSql('DROP TABLE inicio_sesion');
    }
}
