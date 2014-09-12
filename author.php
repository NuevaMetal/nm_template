<?php
require_once (dirname(__FILE__) . '/mvc/controllers/AutorController.php');

$pageController = new AutorController();
$pageController->getAuthor();

?>
