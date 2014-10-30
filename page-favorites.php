<?php
require_once (dirname(__FILE__) . '/mvc/controllers/UserController.php');

$controller = new UserController();
$controller->getFavoritos();
?>