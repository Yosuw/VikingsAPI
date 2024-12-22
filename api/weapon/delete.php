<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/weapon.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/viking.php'; 

header('Content-Type: application/json');

if (!methodIsAllowed('delete')) {
    returnError(405, 'Method not allowed');
    return;
}

if (isset($_GET['id'])) {
    $weaponId = intval($_GET['id']);
    $resetVikings = resetVikingsWeapon($weaponId); 

    if ($resetVikings === false) {
        returnError(500, 'Could not update vikings before deleting weapon');
        return;
    }

    // Supprimer l'arme
    $deleted = deleteWeapon($weaponId);
    if ($deleted == 1) {
        http_response_code(204);
    } elseif ($deleted == 0) {
        returnError(404, 'Weapon not found');
    } else {
        returnError(500, 'Could not delete the weapon');
    }
} else {
    returnError(400, 'Missing parameter : id');
}
