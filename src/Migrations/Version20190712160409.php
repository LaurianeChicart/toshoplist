<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190712160409 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE memo DROP FOREIGN KEY FK_AB4A902A9D86650F');
        $this->addSql('DROP INDEX user_id_id ON memo');
        $this->addSql('ALTER TABLE memo CHANGE item item VARCHAR(255) NOT NULL, CHANGE user_id_id id_user_id INT NOT NULL');
        $this->addSql('ALTER TABLE memo ADD CONSTRAINT FK_AB4A902A79F37AE5 FOREIGN KEY (id_user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_AB4A902A79F37AE5 ON memo (id_user_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE memo DROP FOREIGN KEY FK_AB4A902A79F37AE5');
        $this->addSql('DROP INDEX IDX_AB4A902A79F37AE5 ON memo');
        $this->addSql('ALTER TABLE memo CHANGE item item LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE id_user_id user_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE memo ADD CONSTRAINT FK_AB4A902A9D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX user_id_id ON memo (user_id_id)');
    }
}
