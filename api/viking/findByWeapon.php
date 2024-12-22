
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/viking.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

if (!isset($_GET['id'])) {
    returnError(400, 'Missing parameter: id');
    return;
}

$weaponId = intval($_GET['id']);
$vikings = findVikingsByWeapon($weaponId);

if ($vikings === false) {
    returnError(500, 'Could not retrieve vikings');
    return;
}

if (empty($vikings)) {
    returnError(404, 'No vikings found for this weapon');
    return;
}

$response = [];
foreach ($vikings as $viking) {
    $response[] = [
        'name' => $viking['name'],
        'link' => '/api/viking/details.php?id=' . $viking['id']
    ];
}

echo json_encode($response);
?>
