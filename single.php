<?php
require_once (dirname(__FILE__) . '/mvc/controllers/PostController.php');

$controller = new PostController();
$controller->getPost();


error_log("Single");
dd($_POST);
//Notificar
if(isset($_POST['submit']) && $_POST['submit'] == "Notificar"){
	error_log("Notificar");

}