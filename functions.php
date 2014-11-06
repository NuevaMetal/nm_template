<?php
require_once 'vendor/autoload.php';
require_once 'start.php';

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

$publicDir = get_template_directory_uri() . '/public';
/**
 * Ponemos estilos en el admin
 */
add_action('admin_print_styles', function () use($publicDir) {
	wp_enqueue_style('bootstrap', $publicDir . '/third/bootstrap/css/bootstrap.css');
	wp_enqueue_style('font-awesome', $publicDir . '/third/font-awesome/css/font-awesome.min.css');
	wp_enqueue_style('main', $publicDir . '/css/main.css');
});

/**
 * Ponemos scripts en el admin
 */
add_action('admin_print_scripts', function () use($publicDir) {
	wp_enqueue_script('jquery-plugin', $publicDir . '/third/jquery/jquery.min.js');
	wp_enqueue_script('bootstrap-plugin', $publicDir . '/third/bootstrap/js/bootstrap.min.js');
	wp_enqueue_script('nm', $publicDir . '/js/nm.js');
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