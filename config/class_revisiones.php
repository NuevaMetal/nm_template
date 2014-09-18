<?php

// use Controllers\AnaliticaController ;
require_once (dirname(__FILE__) . '/../mvc/controllers/RevisionesController.php');

// Prevent loading this file directly
defined('ABSPATH') || exit();

define('CR_DIR', plugin_dir_path(__FILE__));
define('CR_INC_DIR', trailingslashit(CR_DIR . 'inc'));

define('CR_URL', plugin_dir_url(__FILE__));
define('CR_CSS_URL', trailingslashit(CR_URL . 'css'));
define('CR_JS_URL', trailingslashit(CR_URL . 'js'));

/**
 * Registramos el hook
 */
//register_activation_hook(__FILE__, 'class_revisiones_activate');


/**
 * Registramos las alertas
 */
add_action('admin_notices', 'class_revisiones_notify');

/**
 * Registramos el menú en para el admin
 */
add_action('admin_menu', function () {
	$page_title = 'Revisiones';
	$num = Revision::getTotalPorRevisar();
	$menu_title = 'Revisiones
		<span class="update-plugins">
			<span class="plugin-count">' . $num . '</span>
		</span>';
	$capability = 'edit_others_posts';
	$menu_slug = 'revisiones';
	$function = 'revisiones_index';
	add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function);
	// Add submenu page with same slug as parent to ensure no duplicates
	$sub_menu_title = 'Revisiones';
	add_submenu_page($menu_slug, $page_title, $sub_menu_title, $capability, $menu_slug, $function);

	$submenu_page_title = 'Usuarios baneados';
	$submenu_title = 'Baneos';
	$submenu_slug = 'class-revisiones-ban';
	$submenu_function = 'class_revisiones_ban';
	add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, $submenu_function);
});

/**
 * Mostrar tablas de revisiones
 */
function revisiones_index() {
	if (!current_user_can('edit_others_posts')) {
		wp_die('You do not have sufficient permissions to access this page.');
	}
	$controller = new RevisionesController();
	$controller->getIndex();
}

/**
 * Mostrar tabla de usuarios baneados en revisiones
 */
function class_revisiones_ban() {
	if (!current_user_can('edit_others_posts')) {
		wp_die('You do not have sufficient permissions to access this page.');
	}
	$controller = new RevisionesController();
	$controller->getBanIndex();
}

/**
 * Mostrar alerta indicando el número total de revisiones pendientes que hay
 */
function class_revisiones_notify() {
	global $wpdb, $current_user;
	$user = User::find($current_user->ID);
	if ($user->isEditor()) {
		$numTotalPorRevisar = Revision::getTotalPorRevisar();
		if (!$numTotalPorRevisar)
			return;
		if ($numTotalPorRevisar == 1) {
			$msg = 'Hay ' . $numTotalPorRevisar . '</span> nueva revisión pendiente';
		} else {
			$msg = 'Hay ' . $numTotalPorRevisar . '</span> nuevas revisiones pendientes';
		}
		$urlRevisiones = admin_url('admin.php?page=revisiones');
		echo '<br>
		<div class="error">
		  <strong>¡Ey ' . $user->display_name . '!</strong> ' . $msg . ' en <a href="' . $urlRevisiones . '">Revisiones</a>
		</div>';
	}
}