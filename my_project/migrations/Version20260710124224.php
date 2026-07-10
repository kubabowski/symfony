<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260710124224 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE shop_order ADD przelewy24_session_id VARCHAR(100) DEFAULT NULL, ADD przelewy24_order_id INT DEFAULT NULL, ADD amount_grosze INT DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_323FC9CAF578F26 ON shop_order (przelewy24_session_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_323FC9CAF578F26 ON shop_order');
        $this->addSql('ALTER TABLE shop_order DROP przelewy24_session_id, DROP przelewy24_order_id, DROP amount_grosze');
    }
}
