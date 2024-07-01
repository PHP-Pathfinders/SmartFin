<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240701195854 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE transaction (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, cash_or_card VARCHAR(10) NOT NULL, transaction_date DATE NOT NULL, money_amount DOUBLE PRECISION NOT NULL, transaction_name VARCHAR(50) NOT NULL, party_name VARCHAR(50) DEFAULT NULL, transaction_notes VARCHAR(255) DEFAULT NULL, INDEX IDX_723705D112469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D112469DE2 FOREIGN KEY (category_id) REFERENCES `categories` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D112469DE2');
        $this->addSql('DROP TABLE transaction');
    }
}
