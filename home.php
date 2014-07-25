<?php
require_once (dirname(__FILE__) . '/mvc/controllers/TestController.php');

/*$siteController = new SiteController();
$siteController->showHome();*/

$siteController = new TestController();
$siteController->homeTest();

?>
