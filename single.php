<?php
require_once (dirname(__FILE__) . '/mvc/controllers/PostController.php');

$controller = new PostController();
$controller->getPost();