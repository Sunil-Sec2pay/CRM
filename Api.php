<?php
// "test.php";
require_once 'config-settings/index.php';

if (!isset($_POST) || empty($_POST)) {
    echo json_encode(["error" => "parameter is required"]);
    exit;
}
if (!isset($_POST['action']) || empty($_POST['action'])) {
    echo json_encode(["error" => "action parameter is required"]);
    exit;
}

?>
