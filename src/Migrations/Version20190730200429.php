<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190730200429 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE bill (id INT AUTO_INCREMENT NOT NULL, description VARCHAR(255) NOT NULL, amount NUMERIC(10, 2) NOT NULL, note LONGTEXT DEFAULT NULL, status VARCHAR(50) NOT NULL, type VARCHAR(50) NOT NULL, due_date DATETIME NOT NULL, payment_date DATETIME DEFAULT NULL, amount_paid NUMERIC(10, 2) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bill_plan (id INT AUTO_INCREMENT NOT NULL, bill_plan_category_id INT NOT NULL, description VARCHAR(255) NOT NULL, INDEX IDX_2E4533D7E7F74BB7 (bill_plan_category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bill_plan_category (id INT AUTO_INCREMENT NOT NULL, description VARCHAR(255) NOT NULL, bill_type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE bill_plan ADD CONSTRAINT FK_2E4533D7E7F74BB7 FOREIGN KEY (bill_plan_category_id) REFERENCES bill_plan_category (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE bill_plan DROP FOREIGN KEY FK_2E4533D7E7F74BB7');
        $this->addSql('DROP TABLE bill');
        $this->addSql('DROP TABLE bill_plan');
        $this->addSql('DROP TABLE bill_plan_category');
    }
}
