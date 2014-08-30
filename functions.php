<?php
require_once ('start.php');

require_once (dirname(__FILE__) . '/lib/chester/require.php');

require_once (dirname(__FILE__) . '/lib/chemaclass/require.php');
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
	AnaliticaController::uninstall();
	FavoritosController::uninstall();
	AnaliticaController::install();
	FavoritosController::install();
});

/**
 * Ponemos estilos en el admin
 */
add_action('admin_print_styles', function () {
	wp_enqueue_style('bootstrap', get_template_directory_uri() . '/public/third/bootstrap/css/bootstrap.css');
	wp_enqueue_style('font-awesome', get_template_directory_uri() . '/public/third/font-awesome/css/font-awesome.min.css');
	wp_enqueue_style('main', get_template_directory_uri() . '/public/css/main.css');
});

add_theme_support('post-thumbnails', array(
	'post',
	'page'
));

show_admin_bar(false);
