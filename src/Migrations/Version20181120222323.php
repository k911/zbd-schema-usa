<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181120222323 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE track_stream ADD contract_id INT NOT NULL');
        $this->addSql('ALTER TABLE track_stream ADD CONSTRAINT FK_B82B7FF52576E0FD FOREIGN KEY (contract_id) REFERENCES music_label_streaming_service_contract (id)');
        $this->addSql('CREATE INDEX IDX_B82B7FF52576E0FD ON track_stream (contract_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE track_stream DROP FOREIGN KEY FK_B82B7FF52576E0FD');
        $this->addSql('DROP INDEX IDX_B82B7FF52576E0FD ON track_stream');
        $this->addSql('ALTER TABLE track_stream DROP contract_id');
    }
}
