<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250529194941 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE comments (
              cid SERIAL NOT NULL,
              pid INT NOT NULL,
              uid INT NOT NULL,
              comment_creation_timestamp TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
              content VARCHAR(16384) NOT NULL,
              PRIMARY KEY(cid)
            )
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_5F9E962A539B0606 ON comments (uid)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_5F9E962A5550C4ED ON comments (pid)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN comments.comment_creation_timestamp IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE posts (
              pid SERIAL NOT NULL,
              tid INT NOT NULL,
              uid INT NOT NULL,
              post_creation_timestamp TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
              title VARCHAR(512) NOT NULL,
              content VARCHAR(8192) NOT NULL,
              PRIMARY KEY(pid)
            )
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_885DBAFA539B0606 ON posts (uid)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_885DBAFA52596C31 ON posts (tid)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN posts.post_creation_timestamp IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE topics (
              tid SERIAL NOT NULL,
              uid INT NOT NULL,
              topic_creation_timestamp TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
              title VARCHAR(512) NOT NULL,
              content VARCHAR(8192) NOT NULL,
              PRIMARY KEY(tid)
            )
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_91F64639539B0606 ON topics (uid)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN topics.topic_creation_timestamp IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              comments
            ADD
              CONSTRAINT FK_5F9E962A539B0606 FOREIGN KEY (uid) REFERENCES users (uid) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              comments
            ADD
              CONSTRAINT FK_5F9E962A5550C4ED FOREIGN KEY (pid) REFERENCES posts (pid) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              posts
            ADD
              CONSTRAINT FK_885DBAFA539B0606 FOREIGN KEY (uid) REFERENCES users (uid) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              posts
            ADD
              CONSTRAINT FK_885DBAFA52596C31 FOREIGN KEY (tid) REFERENCES topics (tid) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              topics
            ADD
              CONSTRAINT FK_91F64639539B0606 FOREIGN KEY (uid) REFERENCES users (uid) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comments DROP CONSTRAINT FK_5F9E962A539B0606
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comments DROP CONSTRAINT FK_5F9E962A5550C4ED
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE posts DROP CONSTRAINT FK_885DBAFA539B0606
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE posts DROP CONSTRAINT FK_885DBAFA52596C31
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE topics DROP CONSTRAINT FK_91F64639539B0606
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE comments
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE posts
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE topics
        SQL);
    }
}
