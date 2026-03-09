<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260220141052 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE adherent ADD utilisateur_id INT NOT NULL, DROP nom, DROP prenom, DROP email');
        $this->addSql('ALTER TABLE adherent ADD CONSTRAINT FK_90D3F060FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_90D3F060FB88E14F ON adherent (utilisateur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE adherent DROP FOREIGN KEY FK_90D3F060FB88E14F');
        $this->addSql('DROP INDEX UNIQ_90D3F060FB88E14F ON adherent');
        $this->addSql('ALTER TABLE adherent ADD nom VARCHAR(100) NOT NULL, ADD prenom VARCHAR(100) NOT NULL, ADD email VARCHAR(255) NOT NULL, DROP utilisateur_id');
    }
}
