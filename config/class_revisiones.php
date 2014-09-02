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
	$allowed_roles = array(
		'editor',
		'administrator'
	);
	if (array_intersect($allowed_roles, $current_user->roles)) {
		$num = Revision::getTotalPorRevisar();
		if (!$num)
			return;
		$admin_revisiones_url = admin_url('admin.php?page=revisiones');
		if ($num == 1) {
			$msg = 'Hay <span class="badge">' . $num . '</span> nueva revisión pendiente';
		} else {
			$msg = 'Hay <span class="badge">' . $num . '</span> nuevas revisiones pendientes';
		}
		echo '
		<br><div class="alert alert-danger alert-dismissible " role="alert">
		  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
		  <strong>¡Ey bro!</strong> ' . $msg . ' en <a href="' . $admin_revisiones_url . '"
						class="alert-link">Revisiones</a>
			</div>';
	}
}