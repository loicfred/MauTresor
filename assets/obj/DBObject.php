<?php

namespace assets\obj;

require_once __DIR__ . "/../../config/database.php";

class DBObject
{
    public static function getByID(int $id = 0) {
        global $pdo;
        $stmt = $pdo->prepare('SELECT * FROM ' . (new \ReflectionClass(static::class))->getShortName() . ' WHERE ID = ?');
        $stmt->execute([$id]);
        return $stmt->fetchObject(static::class);
    }
}