<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181103121740 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE music_label (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, creation_year DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', creator VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE artist (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, age INT UNSIGNED NOT NULL, sex VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE music_label_artist_contract (id INT AUTO_INCREMENT NOT NULL, music_label_id INT NOT NULL, artist_id INT NOT NULL, start_date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', end_date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', INDEX IDX_E2C93B17F4AA731F (music_label_id), INDEX IDX_E2C93B17B7970CF8 (artist_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE music_label_artist_contract ADD CONSTRAINT FK_E2C93B17F4AA731F FOREIGN KEY (music_label_id) REFERENCES music_label (id)');
        $this->addSql('ALTER TABLE music_label_artist_contract ADD CONSTRAINT FK_E2C93B17B7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE music_label_artist_contract DROP FOREIGN KEY FK_E2C93B17F4AA731F');
        $this->addSql('ALTER TABLE music_label_artist_contract DROP FOREIGN KEY FK_E2C93B17B7970CF8');
        $this->addSql('DROP TABLE music_label');
        $this->addSql('DROP TABLE artist');
        $this->addSql('DROP TABLE music_label_artist_contract');
    }
}
