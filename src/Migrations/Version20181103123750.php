<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181103123750 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE track (id INT AUTO_INCREMENT NOT NULL, music_release_id INT NOT NULL, title VARCHAR(255) NOT NULL, isrc VARCHAR(12) NOT NULL, duration INT NOT NULL, edit LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', INDEX IDX_D6E3F8A610292B95 (music_release_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `release` (id INT AUTO_INCREMENT NOT NULL, music_label_id INT NOT NULL, upc BIGINT NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, original_price INT NOT NULL, c_line VARCHAR(255) NOT NULL, p_line VARCHAR(255) NOT NULL, INDEX IDX_9E47031DF4AA731F (music_label_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE release_country (release_id INT NOT NULL, country_id INT NOT NULL, INDEX IDX_8D06B5D9B12A727D (release_id), INDEX IDX_8D06B5D9F92F3E70 (country_id), PRIMARY KEY(release_id, country_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE country (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, iso_code VARCHAR(3) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE track ADD CONSTRAINT FK_D6E3F8A610292B95 FOREIGN KEY (music_release_id) REFERENCES `release` (id)');
        $this->addSql('ALTER TABLE `release` ADD CONSTRAINT FK_9E47031DF4AA731F FOREIGN KEY (music_label_id) REFERENCES music_label (id)');
        $this->addSql('ALTER TABLE release_country ADD CONSTRAINT FK_8D06B5D9B12A727D FOREIGN KEY (release_id) REFERENCES `release` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE release_country ADD CONSTRAINT FK_8D06B5D9F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE track DROP FOREIGN KEY FK_D6E3F8A610292B95');
        $this->addSql('ALTER TABLE release_country DROP FOREIGN KEY FK_8D06B5D9B12A727D');
        $this->addSql('ALTER TABLE release_country DROP FOREIGN KEY FK_8D06B5D9F92F3E70');
        $this->addSql('DROP TABLE track');
        $this->addSql('DROP TABLE `release`');
        $this->addSql('DROP TABLE release_country');
        $this->addSql('DROP TABLE country');
    }
}
