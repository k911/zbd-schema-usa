<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181103130720 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE release_like (id INT AUTO_INCREMENT NOT NULL, customer_id INT NOT NULL, music_release_id INT NOT NULL, customer_ip VARCHAR(255) NOT NULL, INDEX IDX_E194F0A49395C3F3 (customer_id), INDEX IDX_E194F0A410292B95 (music_release_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE track_like (id INT AUTO_INCREMENT NOT NULL, customer_id INT NOT NULL, track_id INT NOT NULL, source VARCHAR(255) NOT NULL, added_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', customer_ip VARCHAR(255) NOT NULL, INDEX IDX_313568B69395C3F3 (customer_id), INDEX IDX_313568B65ED23C43 (track_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE release_like ADD CONSTRAINT FK_E194F0A49395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE release_like ADD CONSTRAINT FK_E194F0A410292B95 FOREIGN KEY (music_release_id) REFERENCES `release` (id)');
        $this->addSql('ALTER TABLE track_like ADD CONSTRAINT FK_313568B69395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE track_like ADD CONSTRAINT FK_313568B65ED23C43 FOREIGN KEY (track_id) REFERENCES track (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE release_like');
        $this->addSql('DROP TABLE track_like');
    }
}
