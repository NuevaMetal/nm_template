<?php
/**
 * Plugin para analíticas y seguimientos de Users y Posts interno
 *
 * @author José María Valera Reales <@Chemaclass>
 * @package Class
 * @since 0.1
 */
define(CLASS_ANALITICA, 'class_analitica');

/**
 * Plugin Name: Class analitica
 * Plugin URI: http://chemaclass.com
 * Description: Plugin para analitica interno
 * Version: 0.1
 * Author: José María Valera Reales
 * Author URI: http://chemaclass.com
 * License: GPL2
 */
require_once (dirname(__FILE__) . '/../mvc/controllers/AnaliticaController.php');

// use Controllers\AnaliticaController ;

if (class_exists('AnaliticaController')) {
	Utils::debug("> ANALITICACONTROLLER SI EXISTE < ");
}
/**
 * Registramos el hook
 */
register_activation_hook(__FILE__, [
	'AnaliticaController',
	'install'
]);
register_deactivation_hook(__FILE__, [
	'AnaliticaController',
	'uninstall'
]);

/**
 * Registramos el menú en para el admin
 */
add_action('admin_menu', function () {
	$page_title = 'Class analitica';
	$menu_title = 'Analitica';
	$capability = 'edit_others_posts';
	$menu_slug = 'class-analitica';
	$function = 'class_analitica_index';
	add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function);
	// Add submenu page with same slug as parent to ensure no duplicates
	$sub_menu_title = 'Analitica';
	add_submenu_page($menu_slug, $page_title, $sub_menu_title, $capability, $menu_slug, $function);
});

/**
 * Mostrar tablas de revisiones
 */
function class_analitica_index() {
	if (!current_user_can('edit_others_posts')) {
		wp_die('You do not have sufficient permissions to access this page.');
	}
	$controller = new AnaliticaController();
	$controller->getIndex();
}

add_action('wp', function () {
	try {
		$analitica = new Analitica();
		$analitica->save();
	} catch (Exception $e) {
		Utils::debug('wp.analitica.save()? ' . $e->getMessage());
	}
});