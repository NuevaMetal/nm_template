<?php
require_once (dirname(__FILE__) . '/mvc/controllers/PageController.php');

$siteController = new SiteController();
$siteController->getPatternPrimer();
?>