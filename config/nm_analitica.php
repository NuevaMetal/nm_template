<?php
/**
 * Plugin para analíticas y seguimientos de Users
 *
 * @author José María Valera Reales <@Chemaclass>
 * @package NuevaMetal
 * @since 0.1
 */
// use Controllers\AnaliticaController ;
require_once (dirname(__FILE__) . '/../mvc/controllers/AnaliticaController.php');

/**
 * Registramos el menú
 */
add_action('admin_menu', function () {
	$page_title = 'Analitica';
	$menu_title = 'Analitica';
	$capability = 'list_users';
	$menu_slug = 'analitica';
	$icon = 'dashicons-chart-line';
	add_menu_page($page_title, $menu_title, $capability, $menu_slug, function () {
		if (!current_user_can('list_users')) {
			wp_die('You do not have sufficient permissions to access this page.');
		}
		$controller = new AnaliticaController();
		$controller->getIndex();
	}, $icon);
});

/**
 * Esta acción será llamada cada vez que se cargue la web
 */
add_action('wp', function () {
	try {
		$analitica = new Analitica();
		$analitica->save();
	} catch (Exception $e) {
		Utils::debug('No se pudo guardar la Analitica ?');
	}
});