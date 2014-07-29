<?php
require_once (dirname(__FILE__) . '/mvc/controllers/BandasController.php');

$controller = new BandasController();
$controller->getBandas();
