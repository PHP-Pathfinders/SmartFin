<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240708181146 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `budgets` (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, category_id INT NOT NULL, monthly_budget DOUBLE PRECISION NOT NULL, monthly_budget_date DATE NOT NULL, INDEX IDX_DCAA9548A76ED395 (user_id), INDEX IDX_DCAA954812469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `categories` (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, category_name VARCHAR(50) NOT NULL, type VARCHAR(10) NOT NULL, color VARCHAR(10) NOT NULL, INDEX IDX_3AF34668A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `transaction_templates` (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, category_id INT DEFAULT NULL, payment_type VARCHAR(10) DEFAULT NULL, money_amount DOUBLE PRECISION DEFAULT NULL, transaction_name VARCHAR(50) DEFAULT NULL, party_name VARCHAR(50) DEFAULT NULL, transaction_notes VARCHAR(255) DEFAULT NULL, INDEX IDX_2C0FE6CDA76ED395 (user_id), INDEX IDX_2C0FE6CD12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `transactions` (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, category_id INT NOT NULL, payment_type VARCHAR(10) NOT NULL, transaction_date DATE NOT NULL, money_amount DOUBLE PRECISION NOT NULL, transaction_name VARCHAR(50) NOT NULL, party_name VARCHAR(50) DEFAULT NULL, transaction_notes VARCHAR(255) DEFAULT NULL, INDEX IDX_EAA81A4CA76ED395 (user_id), INDEX IDX_EAA81A4C12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `users` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, full_name VARCHAR(80) NOT NULL, is_verified TINYINT(1) DEFAULT 0 NOT NULL, password_token VARCHAR(100) DEFAULT NULL, password_token_expires DATETIME DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', birthday DATE DEFAULT NULL, is_active TINYINT(1) DEFAULT 0 NOT NULL, avatar_path VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `budgets` ADD CONSTRAINT FK_DCAA9548A76ED395 FOREIGN KEY (user_id) REFERENCES `users` (id)');
        $this->addSql('ALTER TABLE `budgets` ADD CONSTRAINT FK_DCAA954812469DE2 FOREIGN KEY (category_id) REFERENCES `categories` (id)');
        $this->addSql('ALTER TABLE `categories` ADD CONSTRAINT FK_3AF34668A76ED395 FOREIGN KEY (user_id) REFERENCES `users` (id)');
        $this->addSql('ALTER TABLE `transaction_templates` ADD CONSTRAINT FK_2C0FE6CDA76ED395 FOREIGN KEY (user_id) REFERENCES `users` (id)');
        $this->addSql('ALTER TABLE `transaction_templates` ADD CONSTRAINT FK_2C0FE6CD12469DE2 FOREIGN KEY (category_id) REFERENCES `categories` (id)');
        $this->addSql('ALTER TABLE `transactions` ADD CONSTRAINT FK_EAA81A4CA76ED395 FOREIGN KEY (user_id) REFERENCES `users` (id)');
        $this->addSql('ALTER TABLE `transactions` ADD CONSTRAINT FK_EAA81A4C12469DE2 FOREIGN KEY (category_id) REFERENCES `categories` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `budgets` DROP FOREIGN KEY FK_DCAA9548A76ED395');
        $this->addSql('ALTER TABLE `budgets` DROP FOREIGN KEY FK_DCAA954812469DE2');
        $this->addSql('ALTER TABLE `categories` DROP FOREIGN KEY FK_3AF34668A76ED395');
        $this->addSql('ALTER TABLE `transaction_templates` DROP FOREIGN KEY FK_2C0FE6CDA76ED395');
        $this->addSql('ALTER TABLE `transaction_templates` DROP FOREIGN KEY FK_2C0FE6CD12469DE2');
        $this->addSql('ALTER TABLE `transactions` DROP FOREIGN KEY FK_EAA81A4CA76ED395');
        $this->addSql('ALTER TABLE `transactions` DROP FOREIGN KEY FK_EAA81A4C12469DE2');
        $this->addSql('DROP TABLE `budgets`');
        $this->addSql('DROP TABLE `categories`');
        $this->addSql('DROP TABLE `transaction_templates`');
        $this->addSql('DROP TABLE `transactions`');
        $this->addSql('DROP TABLE `users`');
    }
}
