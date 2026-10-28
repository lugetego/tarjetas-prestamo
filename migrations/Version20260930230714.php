<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260930230714 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tarjeta DROP FOREIGN KEY `FK_AE90B786336FA023`');
        $this->addSql('ALTER TABLE tarjeta ADD CONSTRAINT FK_AE90B786336FA023 FOREIGN KEY (remision_id) REFERENCES remision (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tarjeta DROP FOREIGN KEY FK_AE90B786336FA023');
        $this->addSql('ALTER TABLE tarjeta ADD CONSTRAINT `FK_AE90B786336FA023` FOREIGN KEY (remision_id) REFERENCES remision (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
