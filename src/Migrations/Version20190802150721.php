<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190802150721 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE list_department (id INT AUTO_INCREMENT NOT NULL, list_items_id INT NOT NULL, department INT NOT NULL, INDEX IDX_1730955615DD9DE4 (list_items_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE list_department ADD CONSTRAINT FK_1730955615DD9DE4 FOREIGN KEY (list_items_id) REFERENCES list_items (id)');
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251E3DAE168B');
        $this->addSql('DROP INDEX IDX_1F1B251E3DAE168B ON item');
        $this->addSql('ALTER TABLE item ADD list_department_id INT NOT NULL, DROP list_id, DROP department');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E249FD9D6 FOREIGN KEY (list_department_id) REFERENCES list_department (id)');
        $this->addSql('CREATE INDEX IDX_1F1B251E249FD9D6 ON item (list_department_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251E249FD9D6');
        $this->addSql('DROP TABLE list_department');
        $this->addSql('DROP INDEX IDX_1F1B251E249FD9D6 ON item');
        $this->addSql('ALTER TABLE item ADD department INT NOT NULL, CHANGE list_department_id list_id INT NOT NULL');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E3DAE168B FOREIGN KEY (list_id) REFERENCES list_items (id)');
        $this->addSql('CREATE INDEX IDX_1F1B251E3DAE168B ON item (list_id)');
    }
}
