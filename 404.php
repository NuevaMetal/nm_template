<?php
require_once 'mvc/controllers/PageController.php';

$pageController = new PageController();
$pageController->getError(404);
