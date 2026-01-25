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
    public static function getAllLimitExcept(int $limit = 0, ?string ...$except) {
        global $pdo;
        $reflection = new ReflectionClass(static::class);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
        $table = $reflection->getShortName();

        $selectSql = implode(', ', array_map(function ($prop) {
            return $prop->getName();
        }, array_filter($properties, function ($prop) use ($except) {
            foreach ($except as $ex): if (str_contains($prop->getName(), $ex)) return false;
            endforeach;
            return true;
        })));
        $stmt = $pdo->prepare('SELECT ' . $selectSql . ' FROM ' . $table . ' LIMIT ' . $limit);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS, static::class);
    }
    public static function getAllLimit(int $limit = 0) {
        global $pdo;
        $table = (new \ReflectionClass(static::class))->getShortName();
        $stmt = $pdo->prepare('SELECT * FROM ' . $table . ' LIMIT ' . $limit);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS, static::class);
    }




    public static function selectWhere(?string $select = null, ?string $where = null, ...$object) {
        global $pdo;
        $table = (new \ReflectionClass(static::class))->getShortName();
        $stmt = $pdo->prepare('SELECT ' . ($select != null ? $select : '*') .' FROM ' . $table . ($where != null ? ' WHERE ' . $where : '') . ' LIMIT 1');
        $stmt->execute($object);
        return $stmt->fetchObject(static::class);
    }
    public static function getWhere(?string $where = null, ...$object) {
        global $pdo;
        $table = (new \ReflectionClass(static::class))->getShortName();
        $stmt = $pdo->prepare('SELECT * FROM ' . $table . ($where != null ? ' WHERE ' . $where : '') . ' LIMIT 1');
        $stmt->execute($object);
        return $stmt->fetchObject(static::class);
    }


    public static function selectAllWhere(?string $select = null, ?string $where = null, ...$object) {
        global $pdo;
        $table = (new \ReflectionClass(static::class))->getShortName();
        $stmt = $pdo->prepare('SELECT ' . ($select != null ? $select : '*') .' FROM ' . $table . ($where != null ? ' WHERE ' . $where : ''));
        $stmt->execute($object);
        return $stmt->fetchAll(PDO::FETCH_CLASS, static::class);
    }
    public static function getAllWhere(?string $where = null, ...$object) {
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
            if (str_contains($prop->getType(),"bool")) {
                $fieldValues[] = ($prop->getValue($this) == 1 ? 1 : 0);
            } else {
                $fieldValues[] = $prop->getValue($this) == '' ? null : $prop->getValue($this);
            }
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
            if (str_contains($prop->getType(),"bool")) {
                $fieldValues[] = ($prop->getValue($this) == 1 ? 1 : 0);
            } else {
                $fieldValues[] = $prop->getValue($this) == '' ? null : $prop->getValue($this);
            }
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
            if (str_contains($prop->getType(),"bool")) {
                return ($prop->getValue($this) == 1 ? 1 : 0);
            } else {
                return $prop->getValue($this) === '' ? null : $prop->getValue($this);
            }
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





    public static function getDatabaseDetails(): array {
        global $pdo;
        $dbName = 'treasurehunt';
        $sql = "
        SELECT table_type, COUNT(*) AS total, SUM(TABLE_ROWS) as table_rows
        FROM information_schema.tables
        WHERE table_schema = :db
        GROUP BY table_type
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['db' => $dbName]);
        $result = $stmt->fetchAll();

        $details = ['tables' => 0, 'views' => 0, 'rows' => 0];
        foreach ($result as $row) {
            if ($row['table_type'] === 'BASE TABLE') {
                $details['tables'] = (int)$row['total'];
                $details['rows'] = (int)$row['table_rows'];
            } elseif ($row['table_type'] === 'VIEW') {
                $details['views'] = (int)$row['total'];
            }
        }
        return $details;
    }

    public static function getTableDetails(string $tableName): array {
        global $pdo;
        $dbName = 'treasurehunt';

        $sqlColumns = "
        SELECT COLUMN_NAME, DATA_TYPE, COLUMN_KEY
        FROM information_schema.columns
        WHERE table_schema = :db AND table_name = :table
        ORDER BY ORDINAL_POSITION
        ";
        $stmt = $pdo->prepare($sqlColumns);
        $stmt->execute(['db' => $dbName, 'table' => $tableName]);
        $columns = $stmt->fetchAll();

        $columnCount = count($columns);
        $sqlRows = "
        SELECT table_rows
        FROM information_schema.tables
        WHERE table_schema = :db AND table_name = :table
        ";
        $stmt = $pdo->prepare($sqlRows);
        $stmt->execute(['db' => $dbName, 'table' => $tableName]);
        $row = $stmt->fetch();
        $rowCount = $row ? (int)$row['table_rows'] : 0;
        return [
            'table' => $tableName,
            'columns' => $columnCount,
            'rows' => $rowCount,
            'column_details' => $columns
        ];
    }

}