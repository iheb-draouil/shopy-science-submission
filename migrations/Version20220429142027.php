<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220429142027 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE active_session (id INT AUTO_INCREMENT NOT NULL, app_user_id INT NOT NULL, jwt VARCHAR(255) NOT NULL, started_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_530869CA4A3353D8 (app_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_order (id INT AUTO_INCREMENT NOT NULL, article_id INT NOT NULL, order_number INT NOT NULL, delivery_name VARCHAR(255) NOT NULL, delivery_address VARCHAR(255) NOT NULL, delivery_country VARCHAR(100) NOT NULL, delivery_zipcode VARCHAR(100) NOT NULL, delivery_city VARCHAR(100) NOT NULL, quantity INT NOT NULL, line_price_excl_vat DOUBLE PRECISION NOT NULL, line_price_incl_vat DOUBLE PRECISION NOT NULL, code VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_23FA1E5577153098 (code), INDEX IDX_23FA1E557294869C (article_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON DEFAULT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_88BDF3E9F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(300) NOT NULL, code VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_23A0E665E237E06 (name), UNIQUE INDEX UNIQ_23A0E6677153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE article_instance (id INT AUTO_INCREMENT NOT NULL, article_id INT NOT NULL, code VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_17D845B577153098 (code), INDEX IDX_17D845B57294869C (article_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE active_session ADD CONSTRAINT FK_530869CA4A3353D8 FOREIGN KEY (app_user_id) REFERENCES app_user (id)');
        $this->addSql('ALTER TABLE app_order ADD CONSTRAINT FK_23FA1E557294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE article_instance ADD CONSTRAINT FK_17D845B57294869C FOREIGN KEY (article_id) REFERENCES article (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE active_session DROP FOREIGN KEY FK_530869CA4A3353D8');
        $this->addSql('ALTER TABLE app_order DROP FOREIGN KEY FK_23FA1E557294869C');
        $this->addSql('ALTER TABLE article_instance DROP FOREIGN KEY FK_17D845B57294869C');
        $this->addSql('DROP TABLE active_session');
        $this->addSql('DROP TABLE app_order');
        $this->addSql('DROP TABLE app_user');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE article_instance');
    }
}
