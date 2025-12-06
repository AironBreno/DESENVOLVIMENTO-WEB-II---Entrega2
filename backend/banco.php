<?php
declare(strict_types=1);


const DB_PATH = __DIR__ . '/blogweb.sqlite'; // nome e onde salvar o arquivo sqlite (relativo a este script)



function getConnection(): PDO {
    $pdo = new PDO('sqlite:' . DB_PATH, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,   
    ]);
    // Habilita FKs no SQLite
    $pdo->exec('PRAGMA foreign_keys = ON;');
    return $pdo;
}


function createSchema(PDO $pdo): void {
    $pdo->beginTransaction();

    // Tabela user
    $pdo->exec(<<<SQL
    CREATE TABLE IF NOT EXISTS "user" (
        id             INTEGER PRIMARY KEY AUTOINCREMENT,
        nome           TEXT NOT NULL,
        sobrenome      TEXT NOT NULL,
        senha          TEXT NOT NULL,
        email          TEXT NOT NULL UNIQUE,
        biografia      TEXT,
        avatar_url     TEXT,
        data_registro  TEXT NOT NULL DEFAULT (CURRENT_TIMESTAMP)
    );
    SQL);
}
    // Tabela post
    $pdo->exec(<<<SQL
    CREATE TABLE IF NOT EXISTS post (
        id            INTEGER PRIMARY KEY AUTOINCREMENT,
        titulo        TEXT NOT NULL,
        corpo         TEXT NOT NULL,
        data_criacao  TEXT NOT NULL DEFAULT (CURRENT_TIMESTAMP),
        user_id       INTEGER NOT NULL,
        CONSTRAINT fk_post_user
            FOREIGN KEY (user_id) REFERENCES "user"(id)
            ON DELETE CASCADE ON UPDATE CASCADE
    );
    SQL);

    // Tabela likes (chave primária composta)
    $pdo->exec(<<<SQL
    CREATE TABLE IF NOT EXISTS likes (
        id_user  INTEGER NOT NULL,
        id_post  INTEGER NOT NULL,
        PRIMARY KEY (id_user, id_post),
        CONSTRAINT FK_USER_ID FOREIGN KEY (id_user) REFERENCES "user"(id)
            ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT FK_POST_ID FOREIGN KEY (id_post) REFERENCES post(id)
            ON DELETE CASCADE ON UPDATE CASCADE
    );
    SQL);

    $pdo->commit();




