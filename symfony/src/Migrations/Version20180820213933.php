<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180820213933 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE wb_order (id INT NOT NULL, created_at DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE wb_order_item (id INT AUTO_INCREMENT NOT NULL, order_id INT DEFAULT NULL, item_name VARCHAR(64) NOT NULL, item_price INT DEFAULT NULL, item_postage INT DEFAULT NULL, item_method VARCHAR(32) DEFAULT NULL, INDEX IDX_56BC90548D9F6D38 (order_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE wb_order_item ADD CONSTRAINT FK_56BC90548D9F6D38 FOREIGN KEY (order_id) REFERENCES wb_order (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE wb_order_item DROP FOREIGN KEY FK_56BC90548D9F6D38');
        $this->addSql('DROP TABLE wb_order');
        $this->addSql('DROP TABLE wb_order_item');
    }
}
