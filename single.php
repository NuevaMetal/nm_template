<?php
require_once (dirname(__FILE__) . '/mvc/controllers/PageController.php');

$controller = new PageController();
$controller->getPost();