<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/database.php';

function findOneViking(string $id) {
    $db = getDatabaseConnection();
    $sql = "SELECT 
            v.id, 
            v.name, 
            v.health, 
            v.attack, 
            v.defense, 
            v.weaponId
        FROM 
            viking v
        LEFT JOIN 
            weapon w 
        ON 
            v.weaponId = w.id
        WHERE 
            v.id = :id
    ";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute(['id' => $id]);

    if ($res) {
        $viking = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($viking) {
            $weaponLink = $viking['weaponId'] 
                ? '/api/weapon/findOne.php?id=' . $viking['weaponId'] 
                : "";

            return [
                'id' => $viking['id'],
                'name' => $viking['name'],
                'health' => $viking['health'],
                'attack' => $viking['attack'],
                'defense' => $viking['defense'],
                'weapon' => $weaponLink
            ];
        }
    }

    return null;
}


function findAllVikings(string $name = "", int $limit = 10, int $offset = 0) {
    $db = getDatabaseConnection();
    $params = [];
    $sql = "SELECT 
            v.id, 
            v.name, 
            v.health, 
            v.attack, 
            v.defense, 
            v.weaponId 
        FROM 
            viking v
        LEFT JOIN 
            weapon w 
        ON 
            v.weaponId = w.id
    ";
    if ($name) {
        $sql .= " WHERE v.name LIKE %:name%";
        $params['name'] = $name;
    }
    $sql .= " LIMIT $limit OFFSET $offset";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute($params);

    if ($res) {
        $vikings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($vikings as &$viking) {
            $viking['weapon'] = $viking['weaponId'] 
                ? '/api/weapon/findOne.php?id=' . $viking['weaponId'] 
                : "";
        }

        return $vikings;
    }

    return null;
}


function createViking(string $name, int $health, int $attack, int $defense) {
    $db = getDatabaseConnection();
    $sql = "INSERT INTO viking (name, health, attack, defense) VALUES (:name, :health, :attack, :defense)";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute(['name' => $name, 'health' => $health, 'attack' => $attack, 'defense' => $defense]);
    if ($res) {
        return $db->lastInsertId();
    }
    return null;
}

function updateViking(string $id, string $name, int $health, int $attack, int $defense) {
    $db = getDatabaseConnection();
    $sql = "UPDATE viking SET name = :name, health = :health, attack = :attack, defense = :defense WHERE id = :id";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute(['id' => $id, 'name' => $name, 'health' => $health, 'attack' => $attack, 'defense' => $defense]);
    if ($res) {
        return $stmt->rowCount();
    }
    return null;
}

function deleteViking(string $id) {
    $db = getDatabaseConnection();
    $sql = "DELETE FROM viking WHERE id = :id";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute(['id' => $id]);
    if ($res) {
        return $stmt->rowCount();
    }
    return null;
}

function resetVikingsWeapon($weaponId) {
    $db = getDatabaseConnection();
    $stmt = $db->prepare("UPDATE viking SET weaponId = NULL WHERE weaponId = ?");
    return $stmt->execute([$weaponId]);
}

function findVikingsByWeapon($weaponId) {
    $db = getDatabaseConnection();

    $query = 'SELECT id, name FROM viking WHERE weaponId = ?';
    $stmt = $db->prepare($query);

    if (!$stmt->execute([$weaponId])) {
        return false;
    }

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
