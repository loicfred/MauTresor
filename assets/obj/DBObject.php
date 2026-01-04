<?php

namespace assets\obj;

use PDO;
use ReflectionClass;
use ReflectionProperty;

require_once __DIR__ . "/../../config/database.php";

class DBObject
{
    public int $ID = 0;

    public static function getByID(int $id = 0) {
        global $pdo;
        $table = (new \ReflectionClass(static::class))->getShortName();
        $stmt = $pdo->prepare('SELECT * FROM ' . $table . ' WHERE ID = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetchObject(static::class);
    }
    public static function getAll() {
        global $pdo;
        $table = (new \ReflectionClass(static::class))->getShortName();
        $stmt = $pdo->prepare('SELECT * FROM ' . $table);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS, static::class);
    }

    public static function getWhere(string $where = null, ...$object) {
        global $pdo;
        $table = (new \ReflectionClass(static::class))->getShortName();
        $stmt = $pdo->prepare('SELECT * FROM ' . $table . ($where != null ? ' WHERE ' . $where : '') . ' LIMIT 1');
        $stmt->execute($object);
        return $stmt->fetchObject(static::class);
    }
    public static function getAllWhere(string $where = null, ...$object) {
        global $pdo;
        $table = (new \ReflectionClass(static::class))->getShortName();
        $stmt = $pdo->prepare('SELECT * FROM ' . $table . ($where != null ? ' WHERE ' . $where : ''));
        $stmt->execute($object);
        return $stmt->fetchAll(PDO::FETCH_CLASS, static::class);
    }

    public function Write() {
        global $pdo;
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        $table = $reflection->getShortName();
        $fieldNames  = [];
        $fieldNamesQ  = [];
        $fieldValues = [];
        foreach ($properties as $prop) {
            $fieldNames[]  = $prop->getName();
            $fieldNamesQ[]  = '?';
            $fieldValues[] = $prop->getValue($this);
        }

        $stmt = $pdo->prepare("INSERT INTO " . $table . " (" . implode(', ', $fieldNames) . ") VALUES (" . implode(', ', $fieldNamesQ) . ")");
        $stmt->execute($fieldValues);
        $this->ID = $pdo->lastInsertId();
        return $this;
    }

    public function Upsert() {
        global $pdo;
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        $fieldValues = [];

        $table = $reflection->getShortName();
        $insertFieldNames  = [];
        $insertFieldNamesQ  = [];

        $updateFieldNames  = [];
        foreach ($properties as $prop) {
            $insertFieldNames[]  = $prop->getName();
            $insertFieldNamesQ[]  = '?';
            $fieldValues[] = $prop->getValue($this);
        }

        foreach ($properties as $prop) {
            if ($prop->getName() === 'ID') continue;
            $updateFieldNames[] = $prop->getName() . ' = VALUES(' . $prop->getName() . ')';
        }

        $stmt = $pdo->prepare("INSERT INTO " . $table . " (" . implode(', ', $insertFieldNames) . ") VALUES (" . implode(', ', $insertFieldNamesQ) . ")
        ON DUPLICATE KEY UPDATE " . implode(', ', $updateFieldNames) . ";");
        $stmt->execute($fieldValues);
        $this->ID = $pdo->lastInsertId();
        return $this;
    }

    public function Update() {
        global $pdo;
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        $fieldActualValues = array_map(function ($prop) {
            return $prop->getValue($this);
        }, array_filter($properties, function ($prop) {
            return $prop->getName() !== 'ID';
        }));
        $fieldActualValues[] = $this->ID;

        $table = $reflection->getShortName();
        $setSql = implode(', ', array_map(function ($prop) {
            return $prop->getName() . ' = ?';
        }, array_filter($properties, function ($prop) {
            return $prop->getName() !== 'ID';
        })));

        $stmt = $pdo->prepare('UPDATE ' . $table . ' SET ' . $setSql . ' WHERE ID = ?');
        $stmt->execute($fieldActualValues);
        return $stmt->rowCount();
    }

    public function Delete() {
        global $pdo;
        $table = (new \ReflectionClass($this))->getShortName();
        $stmt = $pdo->prepare('DELETE FROM ' . $table . ' WHERE ID = ?');
        $stmt->execute([$this->ID]);
        return $stmt->rowCount();
    }
}