<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181103130156 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE music_label_streaming_service_contract (id INT AUTO_INCREMENT NOT NULL, music_label_id INT NOT NULL, streaming_service_id INT NOT NULL, start_date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', end_date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', INDEX IDX_87E7AF2FF4AA731F (music_label_id), INDEX IDX_87E7AF2FF3328569 (streaming_service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE track_artist (track_id INT NOT NULL, artist_id INT NOT NULL, INDEX IDX_499B576E5ED23C43 (track_id), INDEX IDX_499B576EB7970CF8 (artist_id), PRIMARY KEY(track_id, artist_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE streaming_service (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transaction (id INT AUTO_INCREMENT NOT NULL, customer_id INT NOT NULL, provider VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', finished_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', customer_ip VARCHAR(255) DEFAULT NULL, INDEX IDX_723705D19395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE release_order (id INT AUTO_INCREMENT NOT NULL, music_release_id INT NOT NULL, transaction_id INT NOT NULL, type VARCHAR(255) NOT NULL, price INT NOT NULL, placed_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_76B7E1EF10292B95 (music_release_id), INDEX IDX_76B7E1EF2FC0CB0F (transaction_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, country VARCHAR(255) NOT NULL, password_hash VARCHAR(255) NOT NULL, joined_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', phone VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE music_label_streaming_service_contract ADD CONSTRAINT FK_87E7AF2FF4AA731F FOREIGN KEY (music_label_id) REFERENCES music_label (id)');
        $this->addSql('ALTER TABLE music_label_streaming_service_contract ADD CONSTRAINT FK_87E7AF2FF3328569 FOREIGN KEY (streaming_service_id) REFERENCES streaming_service (id)');
        $this->addSql('ALTER TABLE track_artist ADD CONSTRAINT FK_499B576E5ED23C43 FOREIGN KEY (track_id) REFERENCES track (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE track_artist ADD CONSTRAINT FK_499B576EB7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D19395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE release_order ADD CONSTRAINT FK_76B7E1EF10292B95 FOREIGN KEY (music_release_id) REFERENCES `release` (id)');
        $this->addSql('ALTER TABLE release_order ADD CONSTRAINT FK_76B7E1EF2FC0CB0F FOREIGN KEY (transaction_id) REFERENCES transaction (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE music_label_streaming_service_contract DROP FOREIGN KEY FK_87E7AF2FF3328569');
        $this->addSql('ALTER TABLE release_order DROP FOREIGN KEY FK_76B7E1EF2FC0CB0F');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D19395C3F3');
        $this->addSql('DROP TABLE music_label_streaming_service_contract');
        $this->addSql('DROP TABLE track_artist');
        $this->addSql('DROP TABLE streaming_service');
        $this->addSql('DROP TABLE transaction');
        $this->addSql('DROP TABLE release_order');
        $this->addSql('DROP TABLE customer');
    }
}
