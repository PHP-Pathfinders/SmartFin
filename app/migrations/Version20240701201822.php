<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240701201822 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `transaction_templates` (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, cash_or_card VARCHAR(10) DEFAULT NULL, money_amount DOUBLE PRECISION DEFAULT NULL, transaction_name VARCHAR(50) DEFAULT NULL, party_name VARCHAR(50) DEFAULT NULL, transaction_notes VARCHAR(255) DEFAULT NULL, INDEX IDX_2C0FE6CD12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `transaction_templates` ADD CONSTRAINT FK_2C0FE6CD12469DE2 FOREIGN KEY (category_id) REFERENCES `categories` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `transaction_templates` DROP FOREIGN KEY FK_2C0FE6CD12469DE2');
        $this->addSql('DROP TABLE `transaction_templates`');
    }
}
