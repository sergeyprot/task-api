<?php

require_once 'config.php';

class Database {
    private static $db;
public static function getConnection() {
    if (self::$db === null) {
        try {
            self::$db = new PDO("sqlite:" . DB_PATH);
            self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    return self::$db;
}
}

?>