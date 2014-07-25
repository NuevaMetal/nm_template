<?php
require_once (dirname(__FILE__) . '/mvc/controllers/SiteController.php');

$siteController = new SiteController();
$siteController->showPatternPrimer();
?>