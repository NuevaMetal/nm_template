<?php
Utils::info("index.php BEGIN");
require_once (dirname(__FILE__) . '/mvc/controllers/PageController.php');

$pageController = new PageController();

$pageController->getIndex();
?>
