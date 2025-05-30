<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250529153458 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` ADD shipping_cost NUMERIC(10, 2) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F09E238517C
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_52EA1F09E238517C ON order_item
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_item CHANGE order_ref_id order_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F098D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_52EA1F098D9F6D38 ON order_item (order_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F098D9F6D38
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_52EA1F098D9F6D38 ON order_item
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_item CHANGE order_id order_ref_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F09E238517C FOREIGN KEY (order_ref_id) REFERENCES `order` (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_52EA1F09E238517C ON order_item (order_ref_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` DROP shipping_cost
        SQL);
    }
}
