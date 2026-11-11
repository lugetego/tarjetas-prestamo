<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261111195500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Corrige las fechas ya guardadas: se registraron en hora UTC en vez de America/Mexico_City (6 horas adelantadas).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE biblioteca SET creado_en = DATE_SUB(creado_en, INTERVAL 6 HOUR)');
        $this->addSql('UPDATE remision SET creado_en = DATE_SUB(creado_en, INTERVAL 6 HOUR)');
        $this->addSql('UPDATE inicio_sesion SET fecha = DATE_SUB(fecha, INTERVAL 6 HOUR)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE biblioteca SET creado_en = DATE_ADD(creado_en, INTERVAL 6 HOUR)');
        $this->addSql('UPDATE remision SET creado_en = DATE_ADD(creado_en, INTERVAL 6 HOUR)');
        $this->addSql('UPDATE inicio_sesion SET fecha = DATE_ADD(fecha, INTERVAL 6 HOUR)');
    }
}
