<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181103131339 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE track_stream (id INT AUTO_INCREMENT NOT NULL, track_id INT NOT NULL, streaming_service_id INT NOT NULL, customer_id INT NOT NULL, started_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', bandwith INT NOT NULL, quality VARCHAR(255) NOT NULL, ended_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_B82B7FF55ED23C43 (track_id), INDEX IDX_B82B7FF5F3328569 (streaming_service_id), INDEX IDX_B82B7FF59395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE track_stream ADD CONSTRAINT FK_B82B7FF55ED23C43 FOREIGN KEY (track_id) REFERENCES track (id)');
        $this->addSql('ALTER TABLE track_stream ADD CONSTRAINT FK_B82B7FF5F3328569 FOREIGN KEY (streaming_service_id) REFERENCES streaming_service (id)');
        $this->addSql('ALTER TABLE track_stream ADD CONSTRAINT FK_B82B7FF59395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE track_stream');
    }
}
