<?php
require_once (dirname(__FILE__) . '/mvc/controllers/HomeController.php');

$controller = new HomeController();
$controller->getHome();

?>
