<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251207010211 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE artist ADD profile_picture_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE artist DROP profile_picture');
        $this->addSql('ALTER TABLE artist ALTER born_at TYPE DATE');
        $this->addSql('ALTER TABLE artist ALTER died_at TYPE DATE');
        $this->addSql('ALTER TABLE artist ADD CONSTRAINT FK_1599687292E8AE2 FOREIGN KEY (profile_picture_id) REFERENCES media_object (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_1599687292E8AE2 ON artist (profile_picture_id)');
        $this->addSql('ALTER TABLE artwork ALTER creation_date TYPE DATE');
        $this->addSql('ALTER TABLE gallery ADD cover_image_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE gallery DROP cover_image');
        $this->addSql('ALTER TABLE gallery ADD CONSTRAINT FK_472B783AE5A0E336 FOREIGN KEY (cover_image_id) REFERENCES media_object (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_472B783AE5A0E336 ON gallery (cover_image_id)');
        $this->addSql('ALTER TABLE media_object ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW() NOT NULL ');
        $this->addSql('ALTER TABLE "user" ADD profile_picture_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" DROP profile_picture');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649292E8AE2 FOREIGN KEY (profile_picture_id) REFERENCES media_object (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_8D93D649292E8AE2 ON "user" (profile_picture_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE artist DROP CONSTRAINT FK_1599687292E8AE2');
        $this->addSql('DROP INDEX IDX_1599687292E8AE2');
        $this->addSql('ALTER TABLE artist ADD profile_picture VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE artist DROP profile_picture_id');
        $this->addSql('ALTER TABLE artist ALTER born_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE artist ALTER died_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE artwork ALTER creation_date TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE gallery DROP CONSTRAINT FK_472B783AE5A0E336');
        $this->addSql('DROP INDEX IDX_472B783AE5A0E336');
        $this->addSql('ALTER TABLE gallery ADD cover_image VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE gallery DROP cover_image_id');
        $this->addSql('ALTER TABLE media_object DROP created_at');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649292E8AE2');
        $this->addSql('DROP INDEX IDX_8D93D649292E8AE2');
        $this->addSql('ALTER TABLE "user" ADD profile_picture VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" DROP profile_picture_id');
    }
}
