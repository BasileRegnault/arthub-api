<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251219125013 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE artist ALTER updated_at DROP NOT NULL');
        $this->addSql('ALTER TABLE media_object ALTER created_at DROP DEFAULT');
        $this->addSql('ALTER TABLE rating DROP CONSTRAINT fk_d8892622db8ffa4');
        $this->addSql('ALTER TABLE rating ADD CONSTRAINT FK_D8892622DB8FFA4 FOREIGN KEY (artwork_id) REFERENCES artwork (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE "user" ALTER updated_at DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE artist ALTER updated_at SET NOT NULL');
        $this->addSql('ALTER TABLE media_object ALTER created_at SET DEFAULT \'now()\'');
        $this->addSql('ALTER TABLE rating DROP CONSTRAINT FK_D8892622DB8FFA4');
        $this->addSql('ALTER TABLE rating ADD CONSTRAINT fk_d8892622db8ffa4 FOREIGN KEY (artwork_id) REFERENCES artwork (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ALTER updated_at SET NOT NULL');
    }
}
