<?php
/**
 * Plugin WP para dar Me gustas a cada Post por cada User
 *
 * @author José María Valera Reales
 * @package MVC Example
 * @since 0.1
 */

/**
 * Plugin Name: Class Favoritos
 * Plugin URI: http://chemaclass.com
 * Description: Seleccionar entradas favoritas, dándole un 'me gusta' a cualquier entrada
 * Version: 0.1
 * Author: José María Valera Reales
 * Author URI: http://chemaclass.com
 * License: GPL2
 */
// use Controllers\AnaliticaController ;
require_once (dirname(__FILE__) . '/../mvc/controllers/FavoritosController.php');

/**
 * Registramos el hook
 */
//register_activation_hook(__FILE__, 'class_favoritos_activate');

/**
 * Registramos las alertas
 */
//add_action('admin_notices', 'class_favoritos_notify');


/**
 * Registramos el menú en para el admin
 */
add_action('admin_menu', function () {
	//http://codex.wordpress.org/Function_Reference/add_menu_page
	//http://codex.wordpress.org/Roles_and_Capabilities
	//http://www.smashingmagazine.com/2011/03/08/ten-things-every-wordpress-plugin-developer-should-know/


	// Add the top-level admin menu
	$page_title = 'Class Favoritos';
	$menu_title = 'Favoritos';
	$capability = 'read';
	$menu_slug = 'class-favoritos';
	$function = 'class_favoritos_index';
	add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function);
	// Add submenu page with same slug as parent to ensure no duplicates
	$sub_menu_title = 'Favoritos';
	add_submenu_page($menu_slug, $page_title, $sub_menu_title, $capability, $menu_slug, $function);
});

/**
 * Añadir los estilos de nuestro tema
 */
// add_action('admin_print_styles', function () {
// 	wp_enqueue_style('class_favoritos', plugin_dir_url(__FILE__) . 'app/public/css/class_favoritos.css');
// });

/**
 * Mostrar tablas de revisiones
 */
function class_favoritos_index() {
	if (!current_user_can('read')) {
		wp_die('You do not have sufficient permissions to access this page.');
	}
	$mainController = new FavoritosController();
	$mainController->getIndex();
}
