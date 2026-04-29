<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'RSTInventar');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

function getDB() : PDO{
    static $pdo = null;
    if($pdo === null){
        try{
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                DB_HOST, DB_NAME, DB_CHARSET
            );
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        }catch(PDOException $e){
            die('Datenbankverbindung fehlgeschlagen: ' . $e->getMessage());
        }
    }
    return $pdo;
}