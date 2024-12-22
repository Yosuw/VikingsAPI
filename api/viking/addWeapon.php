<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/viking.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/weapon.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/viking/service.php';

header('Content-Type: application/json');


if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') {
    returnError(405, 'Method not allowed');
    return;
}


if (!isset($_GET['id'])) {
    returnError(400, 'Dont miss parameter: id');
    return;
}

$vikingId = intval($_GET['id']);
$data = getBody();


if (!isset($data['weaponId'])) {
    returnError(400, 'you need to add parameter: weaponId');
    return;
}

$weaponId = intval($data['weaponId']);


if (!isWeaponExists($weaponId)) {
    returnError(404, 'Weapon not found');
    return;
}


if (!isVikingExists($vikingId)) {
    returnError(404, 'Viking not found');
    return;
}


$updated = updateVikingWeapon($vikingId, $weaponId);

if ($updated) {
    returnSuccess('Weapon added successfully to Viking');
} else {
    returnError(500, 'Could not add the weapon to Viking');
}


