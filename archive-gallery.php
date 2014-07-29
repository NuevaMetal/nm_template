<?php
Utils::info("> archive-gallery.php BEGIN");
require_once (dirname(__FILE__) . '/mvc/controllers/SiteController.php');

$siteController = new SiteController();
$siteController->showGalleries();

?>