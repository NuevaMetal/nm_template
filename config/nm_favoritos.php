<?php
// use Controllers\AnaliticaController ;
//require_once (dirname(__FILE__) . '/../mvc/controllers/FavoritosController.php');

/**
 * Registramos el menú
 */
// add_action('admin_menu', function () {
// 	//http://codex.wordpress.org/Function_Reference/add_menu_page
// 	//http://codex.wordpress.org/Roles_and_Capabilities
// 	//http://www.smashingmagazine.com/2011/03/08/ten-things-every-wordpress-plugin-developer-should-know/


// 	// Add the top-level admin menu
// 	$page_title = 'Favoritos';
// 	$menu_title = 'Favoritos';
// 	$capability = 'read';
// 	$menu_slug = 'favoritos';
// 	$icon = 'dashicons-star-filled';
// 	add_menu_page($page_title, $menu_title, $capability, $menu_slug, function () {
// 		if (!current_user_can('read')) {
// 			wp_die('You do not have sufficient permissions to access this page.');
// 		}
// 		$mainController = new FavoritosController();
// 		$mainController->getIndex();
// 	}, $icon);
// });
