<?php
require_once 'mvc/controllers/HomeController.php';

$homeController = new HomeController();
return $homeController->getHome();
?>
