<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190930125000 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE bloc ADD parent_id INT NOT NULL, ADD filename VARCHAR(255) NOT NULL, ADD libelle VARCHAR(255) NOT NULL, ADD description LONGTEXT DEFAULT NULL, ADD ordre INT DEFAULT NULL, ADD glpicategory INT DEFAULT NULL, ADD information LONGTEXT DEFAULT NULL, ADD type INT NOT NULL, ADD affiche TINYINT(1) NOT NULL, ADD logiciel TINYINT(1) NOT NULL, ADD updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE bloc ADD CONSTRAINT FK_C778955A727ACA70 FOREIGN KEY (parent_id) REFERENCES bloc (id)');
        $this->addSql('CREATE INDEX IDX_C778955A727ACA70 ON bloc (parent_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE bloc DROP FOREIGN KEY FK_C778955A727ACA70');
        $this->addSql('DROP INDEX IDX_C778955A727ACA70 ON bloc');
        $this->addSql('ALTER TABLE bloc DROP parent_id, DROP filename, DROP libelle, DROP description, DROP ordre, DROP glpicategory, DROP information, DROP type, DROP affiche, DROP logiciel, DROP updated_at');
    }
}
