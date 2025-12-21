<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251130034743 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE artwork ADD image_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE artwork DROP file_path');
        $this->addSql('ALTER TABLE artwork ADD CONSTRAINT FK_881FC5763DA5256D FOREIGN KEY (image_id) REFERENCES media_object (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_881FC5763DA5256D ON artwork (image_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE artwork DROP CONSTRAINT FK_881FC5763DA5256D');
        $this->addSql('DROP INDEX IDX_881FC5763DA5256D');
        $this->addSql('ALTER TABLE artwork ADD file_path VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE artwork DROP image_id');
    }
}
