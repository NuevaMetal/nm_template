<?php
spl_autoload_register();
require_once 'start.php';
require_once 'vendor/autoload.php';

require_once ('start.php');

define(URL_PRODUCCION, 'nuevametal.com');
define(URL_DESARROLLO, 'dev.nuevametal.com');

// require_once (dirname(__FILE__) . '/lib/Chester/require.php');
// require_once (dirname(__FILE__) . '/lib/PHPMailer/PHPMailerAutoload.php');

require_once 'mvc/i18n/I18n.php';

foreach (glob(dirname(__FILE__) . '/mvc/lib/*.php') as $filename) {
	require_once $filename;
}
foreach (glob(dirname(__FILE__) . '/mvc/models/*.php') as $filename) {
	require_once $filename;
}

foreach (glob(dirname(__FILE__) . '/config/*.php') as $filename) {
	require_once $filename;
}

/**
 * Instalar las tablas
 */
add_action('after_switch_theme', function () {
	// AnaliticaController::uninstall();
	// FavoritosController::uninstall();
	// RevisionesController::uninstall();
	// AnaliticaController::install();
	// FavoritosController::install();
	// RevisionesController::install();
});

/**
 * Ponemos estilos en el admin
 */
add_action('admin_print_styles', function () {
	$templateDir = get_template_directory_uri();
	wp_enqueue_style('bootstrap', $templateDir . '/public/third/bootstrap/css/bootstrap.css');
	wp_enqueue_style('font-awesome', $templateDir . '/public/third/font-awesome/css/font-awesome.min.css');
	wp_enqueue_style('main', $templateDir . '/public/css/main.css');
});

/**
 * Ponemos scripts en el admin
 */
add_action('admin_print_scripts', function () {
	$templateDir = get_template_directory_uri();
	wp_enqueue_script('jquery-plugin', $templateDir . '/public/third/jquery/jquery.min.js');
	wp_enqueue_script('bootstrap-plugin', $templateDir . '/public/third/bootstrap/js/bootstrap.min.js');
	wp_enqueue_script('nm-plugin', $templateDir . '/public/js/nm-plugin.js');
});

/**
 * Activamos los thumbnails por Post
 */
add_theme_support('post-thumbnails', array(
	'post',
	'page'
));

/**
 * Quitamos la barra del admin en producción
 */
show_admin_bar(false);

@include_once 'test.php';