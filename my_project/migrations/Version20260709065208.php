<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260709065208 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE content ADD text1 LONGTEXT DEFAULT NULL, ADD text2 LONGTEXT DEFAULT NULL, ADD text3 LONGTEXT DEFAULT NULL, ADD text4 LONGTEXT DEFAULT NULL, ADD text5 LONGTEXT DEFAULT NULL, ADD text6 LONGTEXT DEFAULT NULL, ADD image1 VARCHAR(255) DEFAULT NULL, ADD image2 VARCHAR(255) DEFAULT NULL, ADD image3 VARCHAR(255) DEFAULT NULL, ADD image4 VARCHAR(255) DEFAULT NULL, ADD btn_text1 VARCHAR(255) DEFAULT NULL, ADD btn_link1 VARCHAR(255) DEFAULT NULL, ADD btn_text2 VARCHAR(255) DEFAULT NULL, ADD btn_link2 VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE content DROP text1, DROP text2, DROP text3, DROP text4, DROP text5, DROP text6, DROP image1, DROP image2, DROP image3, DROP image4, DROP btn_text1, DROP btn_link1, DROP btn_text2, DROP btn_link2');
    }
}
