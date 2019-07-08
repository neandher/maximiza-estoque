<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190705192823 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE brand (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, state_id INT NOT NULL, email VARCHAR(60) NOT NULL, cnpj VARCHAR(60) NOT NULL, name VARCHAR(255) NOT NULL, phone_number VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_81398E09E7927C74 (email), UNIQUE INDEX UNIQ_81398E09C8C6906B (cnpj), INDEX IDX_81398E09A76ED395 (user_id), INDEX IDX_81398E095D83CC1 (state_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer_state (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE address (id INT AUTO_INCREMENT NOT NULL, uf_id INT DEFAULT NULL, street VARCHAR(255) NOT NULL, district VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, zip_code VARCHAR(50) NOT NULL, complement VARCHAR(255) DEFAULT NULL, INDEX IDX_D4E6F81705D2C5F (uf_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer_addresses (id INT AUTO_INCREMENT NOT NULL, customer_id INT NOT NULL, address_id INT NOT NULL, INDEX IDX_C4378D0C9395C3F3 (customer_id), INDEX IDX_C4378D0CF5B7AF75 (address_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer_brands (id INT AUTO_INCREMENT NOT NULL, brand_id INT NOT NULL, customer_id INT NOT NULL, INDEX IDX_CF67986544F5D008 (brand_id), INDEX IDX_CF6798659395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer_observations (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, customer_id INT NOT NULL, description VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_B51998B1A76ED395 (user_id), INDEX IDX_B51998B19395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE uf (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(2) NOT NULL, region VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E09A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E095D83CC1 FOREIGN KEY (state_id) REFERENCES customer_state (id)');
        $this->addSql('ALTER TABLE address ADD CONSTRAINT FK_D4E6F81705D2C5F FOREIGN KEY (uf_id) REFERENCES uf (id)');
        $this->addSql('ALTER TABLE customer_addresses ADD CONSTRAINT FK_C4378D0C9395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE customer_addresses ADD CONSTRAINT FK_C4378D0CF5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id)');
        $this->addSql('ALTER TABLE customer_brands ADD CONSTRAINT FK_CF67986544F5D008 FOREIGN KEY (brand_id) REFERENCES brand (id)');
        $this->addSql('ALTER TABLE customer_brands ADD CONSTRAINT FK_CF6798659395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE customer_observations ADD CONSTRAINT FK_B51998B1A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE customer_observations ADD CONSTRAINT FK_B51998B19395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE customer_brands DROP FOREIGN KEY FK_CF67986544F5D008');
        $this->addSql('ALTER TABLE customer_addresses DROP FOREIGN KEY FK_C4378D0C9395C3F3');
        $this->addSql('ALTER TABLE customer_brands DROP FOREIGN KEY FK_CF6798659395C3F3');
        $this->addSql('ALTER TABLE customer_observations DROP FOREIGN KEY FK_B51998B19395C3F3');
        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E095D83CC1');
        $this->addSql('ALTER TABLE customer_addresses DROP FOREIGN KEY FK_C4378D0CF5B7AF75');
        $this->addSql('ALTER TABLE address DROP FOREIGN KEY FK_D4E6F81705D2C5F');
        $this->addSql('DROP TABLE brand');
        $this->addSql('DROP TABLE customer');
        $this->addSql('DROP TABLE customer_state');
        $this->addSql('DROP TABLE address');
        $this->addSql('DROP TABLE customer_addresses');
        $this->addSql('DROP TABLE customer_brands');
        $this->addSql('DROP TABLE customer_observations');
        $this->addSql('DROP TABLE uf');
    }
}
