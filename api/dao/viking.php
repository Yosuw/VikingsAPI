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

function getDefaultWeaponId() {
    $db = getDatabaseConnection();

    
    $sql = "SELECT MIN(id) as min_id, MAX(id) as max_id FROM weapon";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $minId = $result['min_id'];
    $maxId = $result['max_id'];

    
    $randomId = rand($minId, $maxId);

    
    $sql = "SELECT id FROM weapon WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute(['id' => $randomId]);
    $weapon = $stmt->fetch(PDO::FETCH_ASSOC);

    
    return $weapon ? $weapon['id'] : null;
}


function updateViking(string $id, string $name, int $health, int $attack, int $defense, ?int $weaponId = null) {
    $db = getDatabaseConnection();

    
    $sql = "UPDATE viking SET name = :name, health = :health, attack = :attack, defense = :defense WHERE id = :id";
    $stmt = $db->prepare($sql);
    $res = $stmt->execute(['id' => $id, 'name' => $name, 'health' => $health, 'attack' => $attack, 'defense' => $defense]);

    if ($res) {
        
        if ($weaponId !== null) {
            
            $weaponExists = checkWeapon($weaponId, $db);

            if ($weaponExists) {
                
                $sql = "UPDATE viking SET weaponId = :weaponId WHERE id = :id";
                $stmt = $db->prepare($sql);
                $stmt->execute(['weaponId' => $weaponId, 'id' => $id]);
            } else {
                
                $sql = "UPDATE viking SET weaponId = NULL WHERE id = :id";
                $stmt = $db->prepare($sql);
                $stmt->execute(['id' => $id]);
            }
        }

        return $stmt->rowCount();
    }

    return null;
}

function checkWeapon(int $weaponId, $db) {
    $sql = "SELECT id FROM weapon WHERE id = :weaponId";
    $stmt = $db->prepare($sql);
    $stmt->execute(['weaponId' => $weaponId]);
    $weapon = $stmt->fetch(PDO::FETCH_ASSOC);
    return $weapon ? true : false;
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

function getDefaultVikingId() {
    $db = getDatabaseConnection();

    try {
        $query = "SELECT id FROM vikings WHERE is_default = 1 LIMIT 1";
        $stmt = $db->query($query);
        $viking = $stmt->fetch();

        return $viking ? $viking['id'] : null;
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération du Viking par défaut : " . $e->getMessage());
        return null;
    }
}

function returnSuccess($message = 'Success') {
    http_response_code(200); 
    echo json_encode(['message' => $message]);
}

function isVikingExists($vikingId) {
    $db = getDatabaseConnection();
    $sql = "SELECT COUNT(*) FROM viking WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute(['id' => $vikingId]);
    return $stmt->fetchColumn() > 0;
}


function isWeaponExists($weaponId) {
    $db = getDatabaseConnection();
    $sql = "SELECT COUNT(*) FROM weapon WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute(['id' => $weaponId]);
    return $stmt->fetchColumn() > 0;
}


function updateVikingWeapon($vikingId, $weaponId) {
    $db = getDatabaseConnection();
    $sql = "UPDATE viking SET weaponId = :weaponId WHERE id = :id";
    $stmt = $db->prepare($sql);
    return $stmt->execute(['weaponId' => $weaponId, 'id' => $vikingId]);
}


