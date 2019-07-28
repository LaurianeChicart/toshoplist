<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190716074404 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE memo DROP FOREIGN KEY FK_AB4A902A79F37AE5');
        $this->addSql('DROP INDEX IDX_AB4A902A79F37AE5 ON memo');
        $this->addSql('ALTER TABLE memo CHANGE id_user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE memo ADD CONSTRAINT FK_AB4A902AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_AB4A902AA76ED395 ON memo (user_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE memo DROP FOREIGN KEY FK_AB4A902AA76ED395');
        $this->addSql('DROP INDEX IDX_AB4A902AA76ED395 ON memo');
        $this->addSql('ALTER TABLE memo CHANGE user_id id_user_id INT NOT NULL');
        $this->addSql('ALTER TABLE memo ADD CONSTRAINT FK_AB4A902A79F37AE5 FOREIGN KEY (id_user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_AB4A902A79F37AE5 ON memo (id_user_id)');
    }
}
