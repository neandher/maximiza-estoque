<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200311230232 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, subtotal NUMERIC(10, 2) NOT NULL, discount NUMERIC(10, 2) NOT NULL, total NUMERIC(10, 2) NOT NULL, client VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_F5299398A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE order_items (id INT AUTO_INCREMENT NOT NULL, order_entity_id INT NOT NULL, referency VARCHAR(255) NOT NULL, quantity SMALLINT NOT NULL, subtotal NUMERIC(10, 2) NOT NULL, discount NUMERIC(10, 2) NOT NULL, total NUMERIC(10, 2) NOT NULL, price NUMERIC(10, 2) NOT NULL, INDEX IDX_62809DB03DA206A5 (order_entity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE order_items ADD CONSTRAINT FK_62809DB03DA206A5 FOREIGN KEY (order_entity_id) REFERENCES `order` (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE order_items DROP FOREIGN KEY FK_62809DB03DA206A5');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE order_items');
    }
}
