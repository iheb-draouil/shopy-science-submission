<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220430160435 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE active_session CHANGE jwt jwt VARCHAR(600) NOT NULL');
        $this->addSql('ALTER TABLE app_order ADD treated TINYINT(1) DEFAULT 0 NOT NULL, CHANGE delivery_name delivery_name VARCHAR(50) NOT NULL, CHANGE delivery_address delivery_address VARCHAR(50) NOT NULL, CHANGE delivery_country delivery_country VARCHAR(50) NOT NULL, CHANGE delivery_zipcode delivery_zipcode VARCHAR(50) NOT NULL, CHANGE delivery_city delivery_city VARCHAR(50) NOT NULL, CHANGE code code VARCHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE app_user DROP roles');
        $this->addSql('ALTER TABLE article CHANGE code code VARCHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE article_instance CHANGE code code VARCHAR(36) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE active_session CHANGE jwt jwt VARCHAR(500) NOT NULL');
        $this->addSql('ALTER TABLE app_order DROP treated, CHANGE delivery_name delivery_name VARCHAR(255) NOT NULL, CHANGE delivery_address delivery_address VARCHAR(255) NOT NULL, CHANGE delivery_country delivery_country VARCHAR(100) NOT NULL, CHANGE delivery_zipcode delivery_zipcode VARCHAR(100) NOT NULL, CHANGE delivery_city delivery_city VARCHAR(100) NOT NULL, CHANGE code code VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE app_user ADD roles JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE article CHANGE code code VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE article_instance CHANGE code code VARCHAR(255) NOT NULL');
    }
}
